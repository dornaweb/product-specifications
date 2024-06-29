<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Metabox;

use WP_Post;
use WP_Post_Type;

final class Metaboxes
{
    public const ACTION_SETUP = 'amiut.product_specs.metaboxes.setup';
    public const NONCE_KEY = 'product_specs_metabox_nonce';

    /**
     * @var array<Metabox>
     */
    private array $metaboxes = [];

    public function __invoke()
    {
        do_action(self::ACTION_SETUP, $this);
    }

    public function add(Metabox ...$metabox): void
    {
        foreach ($metabox as $box) {
            $this->metaboxes[] = $box;
        }
    }

    /**
     * @wp-hook add_meta_boxes
     */
    public function setup(string $postType, WP_Post $post): void
    {
        foreach ($this->metaboxes as $metabox) {
            if (! $metabox->enabled($post)) {
                continue;
            }

            $this->registerMetabox($metabox);
        }
    }

    /**
     * @wp-hook wp_insert_post
     */
    public function save(int $postId, WP_Post $post): void
    {
        foreach ($this->metaboxes as $metabox) {
            if (! $metabox->enabled($post)) {
                continue;
            }


            $this->saveMetabox($post, $metabox);
        }
    }

    private function saveMetabox(WP_Post $post, Metabox $metabox): void
    {
        if ((bool) wp_is_post_autosave($post) || (bool) wp_is_post_revision($post)) {
            return;
        }

        if (! $this->isValid($metabox, $post)) {
            return;
        }

        $metabox->action($post);
    }

    private function registerMetabox(Metabox $metabox): void
    {
        add_meta_box(
            $metabox->id(),
            $metabox->title(),
            function (WP_Post $post) use ($metabox): void {
                echo wp_kses_post(
                    wp_nonce_field(
                        $this->nonceAction($metabox, $post),
                        self::NONCE_KEY
                    )
                );

                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $metabox->render($post);
            },
            null,
            $metabox->context(),
            $metabox->priority(),
        );
    }

    private function nonceAction(Metabox $metabox, WP_Post $post): string
    {
        return "metabox-{$metabox->id()}-{$post->ID}";
    }

    private function isValid(Metabox $metabox, WP_Post $post): bool
    {
        $postType = get_post_type_object($post->post_type);

        if (! $postType instanceof WP_Post_Type) {
            return false;
        }

        if (! current_user_can((string) $postType->cap->edit_post, $post->ID)) {
            return false;
        }

        $nonce = (string) filter_input(INPUT_POST, self::NONCE_KEY, FILTER_SANITIZE_SPECIAL_CHARS);

        if (! (bool) wp_verify_nonce($nonce, $this->nonceAction($metabox, $post))) {
            return false;
        }

        return true;
    }
}
