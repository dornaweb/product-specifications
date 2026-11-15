<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\ProductValues\ProductValueRepository;
use Amiut\ProductSpecs\Specification\Deletion\BackgroundPurge;
use Amiut\ProductSpecs\Specification\Deletion\DeletionImpactRestController;
use Amiut\ProductSpecs\Specification\Deletion\PurgeCommand;
use Amiut\ProductSpecs\Specification\Deletion\PurgeQueue;
use Amiut\ProductSpecs\Specification\Deletion\PurgeRestController;
use Amiut\ProductSpecs\Specification\Deletion\SpecificationDeletionService;
use Amiut\ProductSpecs\Specification\Deletion\ValuePurge;
use Amiut\ProductSpecs\Specification\Rest\KeyAvailabilityRestController;
use Amiut\ProductSpecs\Specification\Rest\SetUsageCounter;
use Amiut\ProductSpecs\Specification\Rest\SlugGuard;
use Amiut\ProductSpecs\Specification\Rest\SpecificationCollectionQuery;
use Amiut\ProductSpecs\Specification\Rest\SpecificationRestField;
use Amiut\ProductSpecs\Specification\Type\Date\DateType;
use Amiut\ProductSpecs\Specification\Type\Document\Attachments;
use Amiut\ProductSpecs\Specification\Type\Document\DocumentType;
use Amiut\ProductSpecs\Specification\Type\Document\WordPressAttachments;
use Amiut\ProductSpecs\Specification\Type\Link\LinkType;
use Amiut\ProductSpecs\Specification\Type\Number\NumberType;
use Amiut\ProductSpecs\Specification\Type\OptionsChecker;
use Amiut\ProductSpecs\Specification\Type\SelectType;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextAreaType;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\Specification\Type\TrueFalseType;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\ProductDataChoicesScript;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\ProductDataSources;
use Amiut\ProductSpecs\Specification\Type\WooCommerce\WooCommerceType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetRepository;
use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;
use WP_CLI;

final class Module implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            SpecificationTaxonomy::class => static fn () => new SpecificationTaxonomy(),
            SpecificationTypeRegistry::class => static fn () => new SpecificationTypeRegistry(new TextType()),
            SpecificationDefinitionMapper::class => static fn () => new SpecificationDefinitionMapper(),
            ProductDataSources::class => static fn () => new ProductDataSources(),
            Attachments::class => static fn () => new WordPressAttachments(),
            ProductDataChoicesScript::class => static fn (
                ContainerInterface $container
            ) => new ProductDataChoicesScript($container->get(ProductDataSources::class)),
            SpecificationDefinitionRepository::class => static fn (ContainerInterface $container) => new SpecificationDefinitionRepository(
                $container->get(SpecificationDefinitionMapper::class)
            ),
            SetUsageCounter::class => static fn (ContainerInterface $container) => new SetUsageCounter(
                $container->get(SpecificationSetRepository::class)
            ),
            PurgeQueue::class => static fn () => new PurgeQueue(),
            SlugAvailability::class => static fn (ContainerInterface $container) => new SlugAvailability(
                $container->get(PurgeQueue::class)
            ),
            SlugGuard::class => static fn (ContainerInterface $container) => new SlugGuard(
                $container->get(SlugAvailability::class),
                $container->get(MigrationState::class)
            ),
            KeyAvailabilityRestController::class => static fn (
                ContainerInterface $container
            ) => new KeyAvailabilityRestController(
                $container->get(SlugAvailability::class),
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(Capabilities::class)
            ),
            SpecificationCollectionQuery::class => static fn () => new SpecificationCollectionQuery(),
            SpecificationRestField::class => static fn (ContainerInterface $container) => new SpecificationRestField(
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(SpecificationTypeRegistry::class),
                $container->get(SetUsageCounter::class),
                $container->get(OptionsChecker::class)
            ),
            OptionsChecker::class => static fn (ContainerInterface $container) => new OptionsChecker(
                $container->get(SpecificationTypeRegistry::class)
            ),
            ValuePurge::class => static fn (ContainerInterface $container) => new ValuePurge(
                $container->get(ProductValueRepository::class)
            ),
            BackgroundPurge::class => static fn (ContainerInterface $container) => new BackgroundPurge(
                $container->get(PurgeQueue::class),
                $container->get(ValuePurge::class)
            ),
            SpecificationDeletionService::class => static fn (
                ContainerInterface $container
            ) => new SpecificationDeletionService(
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(SpecificationSetRepository::class),
                $container->get(ValuePurge::class),
                $container->get(PurgeQueue::class),
                $container->get(BackgroundPurge::class)
            ),
            PurgeRestController::class => static fn (ContainerInterface $container) => new PurgeRestController(
                $container->get(PurgeQueue::class),
                $container->get(BackgroundPurge::class),
                $container->get(Capabilities::class)
            ),
            DeletionImpactRestController::class => static fn (
                ContainerInterface $container
            ) => new DeletionImpactRestController(
                $container->get(SpecificationDefinitionRepository::class),
                $container->get(ProductValueRepository::class),
                $container->get(Capabilities::class)
            ),
            PurgeCommand::class => static fn (ContainerInterface $container) => new PurgeCommand(
                $container->get(PurgeQueue::class),
                $container->get(BackgroundPurge::class)
            ),
        ];
    }

    public function run(ContainerInterface $container): bool
    {
        $container->get(SpecificationCollectionQuery::class)->register();
        $container->get(SpecificationDeletionService::class)->register();
        $container->get(SlugGuard::class)->register();

        add_action(
            SpecificationTypeRegistry::REGISTER_ACTION,
            static function (SpecificationTypeRegistry $registry) use ($container): void {
                $registry->register(new TextType());
                $registry->register(new TextAreaType());
                $registry->register(new SelectType());
                $registry->register(new TrueFalseType());
                $registry->register(new NumberType());
                $registry->register(new WooCommerceType($container->get(ProductDataSources::class)));
                $registry->register(new DocumentType($container->get(Attachments::class)));
                $registry->register(new LinkType());
                $registry->register(new DateType());
            },
            0
        );

        add_action(
            'init',
            static function () use ($container) {
                $taxonomy = $container->get(SpecificationTaxonomy::class);

                register_taxonomy(
                    $taxonomy->key(),
                    SpecificationSetPostType::KEY,
                    $taxonomy->args(
                        $container->get(Capabilities::class)->manageSpecifications()
                    )
                );

                do_action(
                    SpecificationTypeRegistry::REGISTER_ACTION,
                    $container->get(SpecificationTypeRegistry::class)
                );
            }
        );

        add_action(
            'rest_api_init',
            static function () use ($container): void {
                $container->get(SpecificationRestField::class)->register();
                $container->get(PurgeRestController::class)->registerRoutes();
                $container->get(DeletionImpactRestController::class)->registerRoutes();
                $container->get(KeyAvailabilityRestController::class)->registerRoutes();
            }
        );

        add_action(
            BackgroundPurge::HOOK,
            static function () use ($container): void {
                $container->get(BackgroundPurge::class)->process();
            }
        );

        if (class_exists(WP_CLI::class)) {
            WP_CLI::add_command(PurgeCommand::NAME, $container->get(PurgeCommand::class));
        }

        return true;
    }
}
