<?php

namespace Inpsyde\Modularity\Properties {
    interface PluginProperties {
    }
}

namespace Psr\Container
{
    interface ContainerInterface
    {
        /**
         * @template T of object
         * @param string|class-string<T> $id
         * @return ($id is class-string<T> ? T : mixed)
         */
        public function get(string $id): mixed;
    }
}
