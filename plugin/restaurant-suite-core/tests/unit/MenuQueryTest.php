<?php

declare(strict_types=1);

use CRS\Menu\MenuQuery;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/src/Menu/class-menuquery.php';

final class MenuQueryTest extends TestCase
{
    public function testBuildsWooCommerceQueryForAllProducts(): void
    {
        $args = [
            'category' => '',
            'limit' => 12,
            'page' => 2,
            'columns' => 3,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ];

        self::assertSame([
            'status' => 'publish',
            'limit' => 12,
            'page' => 2,
            'paginate' => false,
            'return' => 'objects',
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'visibility' => 'catalog',
        ], MenuQuery::build_args($args));
    }

    public function testAddsValidatedCategorySlugWithoutParallelStorage(): void
    {
        $args = [
            'category' => 'burgers',
            'limit' => 6,
            'page' => 1,
            'columns' => 2,
            'orderby' => 'title',
            'order' => 'DESC',
        ];

        $query = MenuQuery::build_args($args);
        self::assertSame(['burgers'], $query['category']);
        self::assertSame('objects', $query['return']);
        self::assertArrayNotHasKey('table', $query);
    }
}
