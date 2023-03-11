<?php
/**
 * Copyright © 2023 Global Byte - Fabio M. Blanco
 * 
 * This file is part of Author Slug Change.
 * 
 * Author Slug Change is free software: you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of the 
 * License, or (at your option) any later version.
 * 
 * Author Slug Change is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along 
 * with Author Slug Change. If not, see <https://www.gnu.org/licenses/>.
 * 
 */

/**
 * Plugin Name:       Author Slug Change
 * Plugin URI:        https://globalbyte.com/author-slug-change
 * Description:       A tool for dynamically changing the author slug.
 * Version:           0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Global Byte
 * Author URI:        https://globalbyte.com
 * License:           GPL v3
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       author-slug-change
 * Domain Path:       /languages/
 */


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/classes/class-author-slug-change.php';

use Global_Byte\Author_Slug_Change\Author_Slug_Change;

Author_Slug_Change::get_instance();