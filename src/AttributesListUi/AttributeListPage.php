<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\AttributesListUi;

use Amiut\ProductSpecs\Repository\AttributesRepository;
use Amiut\ProductSpecs\Template\TemplateRenderer;

/**
 * @psalm-import-type CollectionArguments from AttributesRepository
 */
final class AttributeListPage
{
    private const DEFAULT_PER_PAGE = 20;

    private TemplateRenderer $renderer;
    private AttributesRepository $repository;

    public function __construct(TemplateRenderer $renderer, AttributesRepository $repository)
    {
        $this->renderer = $renderer;
        $this->repository = $repository;
    }

    public function render(): void
    {
        $collection = $this->repository->findCollection($this->collectionArguments());

        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->renderer->render(
            'admin/attributes/list-page',
            [
                'collection' => $collection,
                'searchKeyword' => $this->searchKeyword(),
                'groupId' => $this->groupId(),
            ]
        );
        // phpcs:enable
    }

    /**
     * @return CollectionArguments
     */
    private function collectionArguments(): array
    {
        $currentPage = absint(filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT)) ?: 1;

        /** @var CollectionArguments $arguments */
        $arguments = [
            'per_page' => absint(get_option('dwps_view_per_page')) ?: self::DEFAULT_PER_PAGE,
            'search_query' => $this->searchKeyword(),
            'current_page' => $currentPage,
        ];

        $groupId = $this->groupId();

        if ($groupId > 0) {
            $arguments['group_id'] = $groupId;
        }

        return $arguments;
    }

    private function searchKeyword(): string
    {
        return (string) filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS);
    }

    private function groupId(): ?int
    {
        return absint(filter_input(INPUT_GET, 'group_id', FILTER_SANITIZE_SPECIAL_CHARS));
    }
}
