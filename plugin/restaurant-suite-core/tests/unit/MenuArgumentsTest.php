<?php

declare(strict_types=1);

use CRS\Menu\MenuArguments;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/src/Menu/class-menuarguments.php';

final class MenuArgumentsTest extends TestCase
{
    public function testNormalizesBoundsAndAllowedValues(): void
    {
        $args = MenuArguments::normalize([
            'category' => 'Burgers & Menus',
            'limit' => 999,
            'page' => 0,
            'columns' => 9,
            'orderby' => 'private_meta',
            'order' => 'DESC',
        ]);

        self::assertSame('burgers-menus', $args['category']);
        self::assertSame(100, $args['limit']);
        self::assertSame(1, $args['page']);
        self::assertSame(4, $args['columns']);
        self::assertSame('menu_order', $args['orderby']);
        self::assertSame('DESC', $args['order']);
    }

    public function testUsesSafeDefaults(): void
    {
        self::assertSame([
            'category' => '',
            'limit' => 12,
            'page' => 1,
            'columns' => 3,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ], MenuArguments::normalize([]));
    }
}
