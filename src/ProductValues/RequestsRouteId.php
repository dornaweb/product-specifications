<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use WP_REST_Request;

trait RequestsRouteId
{
    private function requestId(WP_REST_Request $request): int
    {
        $id = $request->get_param('id');

        return is_numeric($id) ? (int) $id : 0;
    }
}
