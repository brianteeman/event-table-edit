<?php
/**
 * @version		$Id: $
 *
 * @copyright	Copyright (C) 2007 - 2020 Manuel Kaspar and Theophilix
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT.'/helpers/csv.php';

class eventtableeditModelXmlexport extends JModelLegacy
{
    protected $db;
    protected $app;
    protected $id;
    protected $separator;
    protected $doubleqt;
    protected $heads;
    protected $csvData;

    public function __construct()
    {
        parent::__construct();
        $this->db = $this->getDbo();
        $this->app = JFactory::getApplication();
        $this->csvData = [];
    }

    /**
     * Pseudo constructor for setting the variables.
     */
    public function setVariables($id, $separator, $doubleqt, $csvexporttimestamp = 0)
    {
        $this->id = $id;
        $this->separator = $separator;
        $this->doubleqt = $doubleqt;
        $this->csvexporttimestamp = $csvexporttimestamp;
    }

    public function export()
    {
        $this->getHeads();
        $this->getRows();
        $arraysss = [];
        $csvData = $this->csvData;

        for ($a = 0; $a < count($csvData); ++$a) {
            if (0 === (int)$a) { //echo '<pre>';print_r($csvData[$a]);exit;
                $loop = $csvData[$a];
                for ($b = 0; $b < count($loop); ++$b) {
                    $explode = explode('|~|', $loop[$b]);
                    if ('date' === $explode[1]) {
                        $arraysss[] = $b;
                        $csvData[$a][$b] = $explode[0];
                    } else {
                        $csvData[$a][$b] = $explode[0];
                    }
                }
            }
        }
        for ($c = 1; $c < count($csvData); ++$c) {
            $loop1 = $csvData[$c];
            for ($d = 0; $d < count($loop1); ++$d) {
                for ($e = 0; $e < count($arraysss); ++$e) {
                    if ($d === $arraysss[$e]) {
                        if ('0000-00-00' === $loop1[$d] || 'NULL' === $loop1[$d] || '' === $loop1[$d]) {// echo 'IF'.$loop1[$d];
                            $str = '00-00-0000';
                        } else { //echo 'ELSE'.$loop1[$d];
                            $str = date('d-m-Y', strtotime($loop1[$d]));
                        }

                        $csvData[$c][$d] = $str;
                        $str = '';
                    }
                }
            }
        }
        $this->csvData = $csvData;
        $data = Csv::generateCsv($this->separator, $this->doubleqt, $this->csvData);

        $input = JFactory::getApplication()->input;
        $input->set('csvFile', $data);
    }

    /**
     * Get information about the column.
     */
    protected function getHeads()
    {
        $query = 'SELECT CONCAT(\'head_\', a.id) AS head, a.name,a.datatype, a.defaultSorting FROM #__eventtableedit_heads AS a'.
                    ' WHERE a.table_id = '.$this->id.
                    ' ORDER BY a.ordering ASC';
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList();

        // If there are no colums in the table
        if (!count($rows)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_EVENTTABLEEDIT_ERROR_NO_COLUMNS'), 'error');
            return false;
        }

        $this->heads = [];
        $defSort = [];

        foreach ($rows as $row) {
            //if($row->datatype == 'date'){
            $this->csvData[0][] = $row->name.'|~|'.$row->datatype;
            //}
            $this->heads['name'][] = $row->head;

            // Prepare Default Sorting
            if ('' !== $row->defaultSorting && ':' !== $row->defaultSorting) {
                $split = explode(':', $row->defaultSorting);
                $defSort[((int) ($split[0]) - 1)] = $row->head.' '.$split[1];
            }
        }

        if ($this->csvexporttimestamp) {
            $this->csvData[0][] = 'timestamp|~|timestamp';
            $this->heads['name'][] = 'timestamp';
        }

        if (!count($defSort)) {
            $this->heads['defaultSorting'] = 'ordering ASC';
        } else {
            $this->heads['defaultSorting'] = implode(', ', $defSort);
        }
    }

    protected function getRows()
    {
        $query = 'SELECT * FROM #__eventtableedit_rows_'.$this->id.
                 ' ORDER BY '.$this->heads['defaultSorting'];
        $this->db->setQuery($query);
        $rows = $this->db->loadObjectList();

        if (!count($rows)) {
            return false;
        }

        $a = 1;
        foreach ($rows as $row) {
            for ($b = 0; $b < count($this->heads['name']); ++$b) {
                $field = $this->heads['name'][$b];

                $this->csvData[$a][$b] = $row->$field;
            }

            ++$a;
        }
    }

    public function getTabledata($id)
    {
        $db = JFactory::getDBO();
        $select = "SELECT d.*,a.rules FROM #__eventtableedit_details AS d LEFT JOIN #__assets AS a ON d.asset_id=a.id WHERE d.id='".$id."'";
        $db->setQuery($select);
        $tabledata = $db->loadobject();
        return $tabledata;
    }
}
