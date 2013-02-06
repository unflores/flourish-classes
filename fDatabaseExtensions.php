<?php
class Util{
  public function __construct($d_class){
    $this->$d_class = $d_class;
  }

  public function selectFrom($table, $terms){
    list($sql_select, $sql_terms, $order_clause, $sql_limit) = self::_buildSelect($terms);
    $sql = "SELECT $sql_select FROM $table WHERE $sql_terms $order_clause $sql_limit";

    return fORMDatabase::retrieve()->query($sql)->fetchAllRows();
  }


  public static function _buildSelect($terms){
    if(isset($terms['limit']) and isset($terms['offset'])){
      $sql_limit = "limit {$terms['offset']}, {$terms['limit']}";
    }elseif(isset($terms['limit'])){
      $sql_limit = "limit {$terms['limit']}";
    }else{
      $sql_limit = '';
    }
    unset($terms['limit']);
    unset($terms['offset']);

    $order_clause = isset($terms['order_by'])? "order by {$terms['order_by']}": '';
    unset($terms['order_by']);

    $and_clause = isset($terms['and'])? $terms['and'] : "";
    unset($terms['and']);

    $or_clause = isset($terms['or'])? $terms['or'] : "";
    $or_clause = '';
    if(isset($terms['or']) and is_array($terms['or'])){
      $or_clause = ' OR ' . implode(" OR ", $terms['or']);
    }elseif(! empty($terms['or'])){
      $or_clause = " OR {$terms['or']}";
    }

    unset($terms['or']);

    $sql_select = empty($terms['select']) ? '*' : $terms['select'];
    unset($terms['select']);

    foreach($terms as $key => $value){
      if($value === null){
        $terms[$key] = "$key IS NULL";
      }else{
        $terms[$key] = "$key = ".pSQL($value);
      }
    }

    $sql_terms = implode(' AND ', array_filter(array_merge($terms, array($and_clause))));
    $sql_terms = $sql_terms ? $sql_terms : 1;

    $sql_terms = $sql_terms && !empty($or_clause) ? "$sql_terms $or_clause": $sql_terms;
    return array($sql_select,  $sql_terms, $order_clause, $sql_limit);
  }
}