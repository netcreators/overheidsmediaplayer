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

namespace Netcreators\Overheidsmediaplayer\Sanitization;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Sanitization class.
 *
 * @author Leonie Philine Bitto <leonie@netcreators.nl>, Patrick Broens
 */
class Sanitizer
{
    /**
     * The incoming variables
     *
     * @var array
     */
    private $piVars;

    /**
     * Allowed incoming variables and their types
     *
     * @var array
     */
    private $usedPiVars = [
        'action' => [
            'values' => [
                'file'
            ],
            'default' => ''
        ],
        'file' => 'integer',
        'sHash' => [
            'securefile' => [
                'action',
                'file',
            ]
        ]
    ];

    /**
     * Sanitize all allowed incoming variables, based on their type
     *
     * @param array $piVars Unsanitized incoming variables
     * @return array Contains the allowed and sanitized values
     */
    public function sanitize($piVars)
    {
        $this->piVars = $piVars;
        return $this->sanitizeArray($piVars, $this->usedPiVars);
    }

    /**
     * Sanitize only one variable .
     * Returns the variable sanitized according to the desired type or true/false
     * for certain data types if the variable does not correspond to the given data type.
     *
     * @param mixed $variable The variable itself
     * @param string $type A string containing the desired variable type
     * @return mixed The sanitized variable or true/false
     */
    private function sanitizeOne($variable, $type)
    {
        $variable = GeneralUtility::removeXSS($variable);
        switch (true) {
            case $type === 'integer': // integer
                $variable = (int)$variable;
                break;
            case is_array($type):
                if (isset($type['values'])) {
                    if (in_array($variable, $type['values'])) { // Compare with allowed values, otherwise empty
                        $variable = htmlentities(trim($variable), ENT_COMPAT);
                    } else {
                        $variable = $type['default'];
                    }
                } elseif (isset($type['securefile'])) { // Check the posted hash with the hash it should be
                    $hashFromUrl = (string)$variable;
                    $variable = false;
                    $stringFromVariables = '';
                    foreach ($type['securefile'] as $key) {
                        $stringFromVariables .= $this->piVars[$key];
                    }
                    $stringFromVariables .= $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];

                    $hashFromVariables = (string)substr(md5($stringFromVariables), 0, 8);

                    if ($hashFromUrl === $hashFromVariables) {
                        $variable = true;
                    }
                }
                break;
        }

        return $variable;
    }

    /**
     * Sanitize an array.
     *
     * @param array $data The incoming array
     * @param array $whatToKeep The allowed variables
     * @return array The sanitized array
     */
    private function sanitizeArray($data, $whatToKeep)
    {
        $data = array_intersect_key($data, $whatToKeep);
        foreach ($data as $key => $value) {
            $data[$key] = $this->sanitizeOne($data[$key], $whatToKeep[$key]);
        }
        return $data;
    }
}

