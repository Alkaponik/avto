<?php
class Testimonial_System_Model_Resource_Helper_Mysql4 extends Mage_Core_Model_Resource_Helper_Mysql4
{
    public function insertMultipleOnDuplicate($tableName, $data, $updateFields = array())
    {

        if(empty($data)) {
            return 0;
        }
        $adapter = $this->_getWriteAdapter();
        $tableName = $adapter->quoteIdentifier($tableName, true);
        $columns = array_map(array($adapter, 'quoteIdentifier'), array_keys(reset($data)));
        $columns = implode(',', $columns);
        foreach ($data as &$row) {
            $row = array_map(array($adapter, 'quote'), $row);
            $row = '(' . implode(',', $row) . ')';
        }
        $values = implode(', ', $data);

        $insertSql = sprintf('INSERT INTO %s (%s) VALUES %s', $tableName, $columns, $values);

        $update = array();
        foreach ($updateFields as $field) {
            $update[] = sprintf('%1$s = VALUES(%1$s)', $adapter->quoteIdentifier($field));
        }

        if ($updateFields) {
            $insertSql = sprintf('%s ON DUPLICATE KEY UPDATE %s', $insertSql, join(', ', $update));
        }

        $result = $adapter->query($insertSql);
        return $result->rowCount();
    }

    public function insertFromSelectOnDuplicate(
        Varien_Db_Select $select, $table, array $fields = array(), array $updateFields = array()
    ) {
        $adapter = $this->_getWriteAdapter();
        $query = 'INSERT';

        $query = sprintf('%s INTO %s', $query, $adapter->quoteIdentifier($table));
        if ($fields) {
            $columns = array_map(array($adapter, 'quoteIdentifier'), $fields);
            $query = sprintf('%s (%s)', $query, join(', ', $columns));
        }

        $query = sprintf('%s %s', $query, $select->assemble());

        if (!$fields) {
            $describe = $adapter->describeTable($table);
            foreach ($describe as $column) {
                if ($column['PRIMARY'] === false) {
                    $fields[] = $column['COLUMN_NAME'];
                }
            }
        }

        $updateFields = array_intersect($updateFields, $fields);
        $update = array();
        foreach ($updateFields as $field) {
            $update[] = sprintf('%1$s = VALUES(%1$s)', $adapter->quoteIdentifier($field));
        }

        if ($updateFields) {
            $query = sprintf('%s ON DUPLICATE KEY UPDATE %s', $query, join(', ', $update));
        }

        return $query;
    }

}