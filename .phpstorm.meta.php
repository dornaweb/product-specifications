<?php

namespace PHPSTORM_META {

    override(
        \Psr\Container\ContainerInterface::get(0),
        map([
            \Inpsyde\Modularity\Package::PROPERTIES => \Inpsyde\Modularity\Properties\PluginProperties::class,
            '' => '@',
        ])
    );
}
