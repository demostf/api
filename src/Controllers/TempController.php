<?php
/**
 * @copyright Copyright (c) 2018 Robin Appelman <robin@icewind.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Demostf\API\Controllers;


class TempController extends BaseController {
    private $webRoot;

    public function __construct(string $webRoot) {
        $this->webRoot = $webRoot;
    }

    public function register(string $key, string $path) : string {
        apcu_store($key, $path);
        return $this->webRoot . $key;
    }

    public function unregister(string $key) {
        apcu_dec($key);
    }

    public function serve(string $key) {
        $path = apcu_fetch($key);
        if ($path) {
            $handle = fopen($path, 'r');
            fpassthru($handle);
            fclose($handle);
        } else {
            \Flight::response()
                ->status(404)
                ->send();
        }
    }
}
