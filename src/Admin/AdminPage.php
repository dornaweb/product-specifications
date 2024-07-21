<?php

namespace Amiut\ProductSpecs\Admin;

class AdminPage
{
    public const FILTER_SPECS_TABLES_ADMIN_PAGES_CAPABILITIES = 'dornaweb.specs_table.admin_pages_capability';

    private string $pageTitle;
    private string $menuTitle;
    private string $menuSlug;
    private string $menuIcon;
    private int $position;
    private string $capabilityName;
    private ?self $parent = null;

    private function __construct(
        string $pageTitle,
        string $menuTitle,
        string $menuSlug,
        string $menuIcon,
        int $position = 25,
        string $capabilityName = 'manage_options',
        ?self $parent = null
    ) {
        $this->pageTitle = $pageTitle;
        $this->menuTitle = $menuTitle;
        $this->menuSlug = $menuSlug;
        $this->menuIcon = $menuIcon;
        $this->position = $position;
        $this->capabilityName = $capabilityName;
        $this->parent = $parent;
    }

    public static function new(string $pageTitle, string $menuTitle, string $menuSlug, string $menuIcon): self
    {
        $instance = new self($pageTitle, $menuTitle, $menuSlug, $menuIcon);
        $instance->capabilityName = apply_filters(
            self::FILTER_SPECS_TABLES_ADMIN_PAGES_CAPABILITIES,
            'manage_options'
        );

        return $instance;
    }

    public function addSubPage(AdminPage $subPage): self
    {
        $subPage->parent = $this;
        return $this;
    }
}
