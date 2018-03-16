<?php
/*  Copyright notice
 *
 *  (c) 2009 Patrick Broens
 *  (c) 2013-2017 Leonie Philine Bitto <leonie@netcreators.nl>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace Netcreators\Overheidsmediaplayer\View;

use Netcreators\Overheidsmediaplayer\Controller\VideoController;

/**
 * The view abstract.
 *
 * @author Leonie Philine Bitto <leonie@netcreators.nl>, Patrick Broens
 */
abstract class AbstractView
{

    /**
     * The current controller
     *
     * @var VideoController
     */
    protected $controller;

    /**
     * Sets the controller
     *
     * @param VideoController $controller
     * @return void
     */
    public function setController(VideoController $controller)
    {
        $this->controller = $controller;
    }
}

