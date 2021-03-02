<?php
/**
 * EAD 3 Record Class
 *
 * PHP version 7
 *
 * Copyright (C) The National Library of Finland 2012-2021.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category DataManagement
 * @package  RecordManager
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Jukka Lehmus <jlehmus@mappi.helsinki.fi>
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/NatLibFi/RecordManager
 */
namespace RecordManager\NKR\Record;

use RecordManager\Base\Database\DatabaseInterface as Database;
use RecordManager\Base\Utils\Logger;

/**
 * EAD 3 Record Class
 *
 * EAD 3 records with NKR-Finna specific functionality
 *
 * @category DataManagement
 * @package  RecordManager
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Jukka Lehmus <jlehmus@mappi.helsinki.fi>
 * @author   Samuli Sillanpää <samuli.sillanpaa@helsinki.fi>
 * @author   Juho Lehtonen <juho.lehtonen@csc.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/NatLibFi/RecordManager
 */
class Ead3 extends \RecordManager\Finna\Record\Ead3
{
    /**
     * Return fields to be indexed in Solr
     *
     * @param Database $db Database connection. Omit to avoid database lookups for
     *                     related records.
     *
     * @return array
     */
    public function toSolrArray(Database $db = null)
    {
        $data = parent::toSolrArray($db);
        $data['_document_id'] = $this->getUnitId();
        
        $harvest_mode = $this->getDriverParam('harvest_mode', 'unset');
        if ($harvest_mode === 'unset') {
            $this->logger->log(
                'toSolrArray',
                "Harvest mode not set in 'datasources.ini'",
                Logger::FATAL
            );
            throw new \Exception('No harvest mode set in datasources.ini');
        } elseif ($harvest_mode === 'ahaa_open') {
            // NOTE: The value must be "00" because "0" gets mixed to PHP's "false" boolean value. This causes the
            // whole key value pair to be ignored and not indexed at all, that is, key will be missing in Solr.
            $data['display_restriction_id_str'] = '00';
        } elseif ($harvest_mode === 'ahaa_restricted') {
            $data['_document_id'] .= '::10';
            $data['display_restriction_id_str'] = '10';
        }

        return $data;
    }
}
