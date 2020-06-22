<?php
/**
 * EAD 3 Record Class
 *
 * PHP version 5
 *
 * Copyright (C) The National Library of Finland 2012-2019.
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
 * @link     https://github.com/KDK-Alli/RecordManager
 */
namespace RecordManager\NKR\Record;

use RecordManager\Base\Utils\Logger;
use RecordManager\Base\Utils\MetadataUtils;

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
 * @link     https://github.com/KDK-Alli/RecordManager
 */
class Ead3 extends \RecordManager\Finna\Record\Ead3
{
    /**
     * Return fields to be indexed in Solr (an alternative to an XSL transformation)
     *
     * @return array
     */
    public function toSolrArray()
    {
        $data = parent::toSolrArray();
        $data['_document_id'] = $this->getUnitId();
        
        // TODO: move setting harvest mode to harvest process initialization
        $harvest_mode = $this->getDriverParam('harvest_mode', 'unset');
        if ($harvest_mode === 'unset') {
            $this->logger->log(
                'toSolrArray',
                "Harvest mode not set in 'datasources.ini'",
                Logger::FATAL
            );
            throw new \Exception('No harvest mode set in datasources.ini');
        } elseif ($harvest_mode === 'ahaa_open') {
            $data['display_restriction_id_str'] = '00';
        } elseif ($harvest_mode === 'ahaa_restricted') {
            $data['_document_id'] .= '::10';
            $data['display_restriction_id_str'] = '10';
        }
        
        // TODO: maybe 'harvest_mode_str' isn't needed in index
        $data['harvest_mode_str'] = $harvest_mode;               
        return $data;
    }
}
?>
