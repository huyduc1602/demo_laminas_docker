<?php
namespace Zf\Ext\Model;
use Laminas\Db\Sql\Sql;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;

class ZFDbAdapter {
    /**
     * Get data from Database
     * 
     * @param \Laminas\Db\Adapter\Adapter $zAdapter
     * @param string $tbl
     * @param array $opts
     *  <p>cols: array</p>
     *  <p>wheres: array</p>
     *  
     * @return \Laminas\Db\Adapter\Driver\StatementInterface|\Laminas\Db\ResultSet\ResultSet
     */
    public static function select( $zAdapter, string $tbl, $opts = array() ){
        // Create Service
        $sqlService = new Sql($zAdapter);
        
        // Create query
        $sql = $sqlService->select();
        $sql->from($tbl);
        
        // Get data
        if ( isset($opts['cols']) && count($opts['cols']) > 0 )
            $sql->columns( (array)$opts['cols'] );
        
        // Where condition
        if ( $opts['wheres'] ) $sql->where($opts['wheres']);
        
        // -- Paginator
        if ( isset($opts['paginated']) && true == $opts['paginated'] ){
            // create a new result set based on the Album entity
            $resultSet = new \Laminas\Db\ResultSet\HydratingResultSet(null, null);
            
            // create a new pagination adapter object
            $paginatorAdapter = new DbSelect(
                // our configured select object
                $sql,
                // the adapter to run it against
                $zAdapter,
                // the result set to hydrate
                $resultSet
            );
            $paginator = new Paginator($paginatorAdapter);
            return $paginator;
        }
        
        // -- Get server mail config.
        $selectString = $sql->getSqlString($zAdapter->getPlatForm());
        unset($sqlService);
        return $zAdapter->query($selectString, $zAdapter::QUERY_MODE_EXECUTE);
    }
    
    /**
     * Get data from Database
     *
     * @param \Laminas\Db\Adapter\Adapter $zAdapter
     * @param string $tbl
     * @param array $opts
     *
     * @return \Laminas\Db\Adapter\Driver\StatementInterface|\Laminas\Db\ResultSet\ResultSet
     */
    public static function findOne( $zAdapter, string $tbl, $opts = array() ){
        return self::select($zAdapter, $tbl, $opts)->current();
    }
    
    /**
     * Get data from Database
     *
     * @param \Laminas\Db\Adapter\Adapter $zAdapter
     * @param string $tbl
     * @param array $opts
     *
     * @return \Laminas\Db\Adapter\Driver\StatementInterface|\Laminas\Db\ResultSet\ResultSet
     */
    public static function findBy( $zAdapter, string $tbl, $opts = array() ){
        return self::select($zAdapter, $tbl, $opts);
    }
    
    /**
     * Update data
     *
     * @param \Laminas\Db\Adapter\Adapter $zAdapter
     * @param string $tbl
     * @param array $opts
     *  <p>values: array</p>
     *  <p>wheres: array</p>
     *
     * @return integer
     */
    public static function update( $zAdapter, string $tbl, $opts = array() ){
        if ( !isset($opts['values']) ) return 0;
        
        // Create Service
        $sqlService = new Sql($zAdapter);
        
        $update = $sqlService
            ->update($tbl)
            ->set($opts['values'])
        ;
        
        // -- Where condition
        if ( isset($opts['wheres']) ) $update->where($opts['wheres']);
        
        // -- Get result
        $rs = $sqlService->prepareStatementForSqlObject($update)->execute();
        
        unset($sqlService);
        return $rs->getAffectedRows();
    }
    
    /**
     * Insert data
     *
     * @param \Laminas\Db\Adapter\Adapter $zAdapter
     * @param string $tbl
     * @param array $opts
     *  <p>columns: array</p>
     *  <p>values: array</p>
     *
     * @return integer
     */
    public static function insert( $zAdapter, string $tbl, $opts = array() ){
        if ( !isset($opts['columns']) ) return 0;
    
        // Create Service
        $sqlService = new Sql($zAdapter);
    
        $insert = $sqlService
            ->insert($tbl)
            ->columns($opts['columns'])
            ->values($opts['values'])
        ;
    
        // -- Get result
        $rs = $sqlService->prepareStatementForSqlObject($insert)->execute();
    
        unset($sqlService);
        return $rs->getAffectedRows();
    }
}
?>