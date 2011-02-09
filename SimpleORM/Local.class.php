<?php

/**
 * SimpleORM__Local - SimpleORM customizations for Local
 *
 * So far SimpleORM has been written to not be Local-specific,
 * so it could be submittable to an open-source repo like PEAR.
 * Anyhow, for that and other design reasons, it's separated.
 *
 * For practical purposes it supplies the database handle and
 * several custom methods that relate to how database tables are
 * commonly designed in the Local system, especially with the
 * use of the 'status' and 'inactive_date' fields, and aldo for a
 * "disabled" status value and a 'disabled_date' column.
 */
class SimpleORM__DAX__Local extends SimpleORM {
    /**
     * provide_dbh() - Internal method to give a database handle to each SimplORM-derived objects for Local
     */
    protected function provide_dbh() { return $GLOBALS['DAX_DBH']; }

    /**
     * provide_db_type() - Internal method to give a database handle to each SimplORM-derived objects for Local
     */
    protected function provide_db_type() { return $GLOBALS['DAX_DB_TYPE']; }

}