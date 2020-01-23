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
        // $doc = $this->doc;

        $data['_document_id'] = $this->getUnitId();
        $nrStatus = '';

        /* Check if the overall containing archive contains restricted content */
        if ($this->doc->{'add-data'}->archive) {
            $archiveAttr = $this->doc->{'add-data'}->archive->attributes();
            $nrStatus = (string)$archiveAttr->{'nr-status'};
            if (!$nrStatus) {
                // FIXME: Remove this: non-empty string to index the value for development
                $nrStatus = 'FIXME_DEV';
                // FIXME: Remove "if" here. If attribute missing we reject the record.
                if ($nrStatus != 'FIXME_DEV' ) {
                    $this->logger->log(
                        'Ead3',
                        'Failed to find $nrStatus, unable to infer NR status',
                        Logger::FATAL
                    );
                    throw new \Exception('Failed to find $nrStatus, unable to infer NR status');
                }
            }
            // FIXME: Remove "if" here. Development helper
            if ($nrStatus == 'FIXME_DEV' ) {
                $data['nr_status_str'] = $nrStatus;
                $data['_document_id'] .= '::10';
                $data['display_restriction_id_str'] = '10';
                $nodes = $this->doc->xpath('//[@relator]');
                $data['nr_verification_str'] = $nodes;
            } 
            if ($nrStatus == 'NR10' ) {
                $data['_document_id'] .= '::10';
                $data['display_restriction_id_str'] = '10';
            } else {
                /* Check if the current archive sub-unit contains restricted elements */ 
                $nodes = $this->doc->xpath('//[@displayRestrictionId]');
                if ($nodes) {
                    $this->logger->log(
                        'Ead3',
                        'Found restricted attributes inside non-restricted record!',
                        Logger::FATAL
                    );
                    throw new \Exception('Found restricted attributes inside non-restricted record!');
                }
                $data['display_restriction_id_str'] = '00';
            }
        }
        return $data;
    }
}
?>