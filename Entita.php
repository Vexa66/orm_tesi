<?php

abstract class Entita
{

  protected $tabella;
  protected $db;

  public function __construct()
  {
    try {
      $this->db = new \PDO('mysql:host=localhost;dbname=blog', 'root', '');
    } catch (\Exception $e) {
      throw new \Exception('Errore nello stabilire una connessione con il database ');
    }
  }

  public function salva($idFound = 0)
  {

    if(intval($idFound) > 0) {
      $this->id = $idFound;
    }

    $class = new \ReflectionClass($this);
    $tabella = '';

    if ($this->tabella != '') {
      $tabella = $this->tabella;
    } else {
      $tabella = strtolower($class->getShortName());
    }

    $propsToImplode = [];

    foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) { 
      $nameProperty = $property->getName();
      $propsToImplode[] = '`' . $nameProperty . '` = "' . $this->{$nameProperty} . '"';
    }

    $clauseSection = implode(',', $propsToImplode);
    $sqlQuery = '';

    if ($this->id > 0) {
      $sqlQuery = 'UPDATE `' . $tabella . '` SET ' . $clauseSection . ' WHERE id = ' . $this->id;
    } else {
      $sqlQuery = 'INSERT INTO `' . $tabella . '` SET ' . $clauseSection;
    }

    if(isset($_GET['test'])) {
      die($sqlQuery);
    }

    $stmt = $this->db->prepare("Select * from $tabella where id = $idFound");
    $stmt->execute();
    $raw = $stmt->fetchAll();

    if(count($raw) > 0 OR $idFound == 0) {
      $result = $this->db->exec($sqlQuery);

      if (intval($this->db->errorCode()) > 0) {
        throw new \Exception(json_encode($this->db->errorInfo()));
      }

      if(count($raw) > 0) {
        if(intval($idFound) > 0) {
          return $idFound;
        }
      } else {
        if(intval($this->db->lastInsertId()) > 0) {
          $this->id = $this->db->lastInsertId();
          return $this->id;
        }
      }

      
    } else {
      return 'Nessuna operazione effettuata';
    }
  }

  public function plasma(array $object)
  {
    $class = new \ReflectionClass(get_called_class());

    $entita = $class->newInstance();

    foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
      if (isset($object[$prop->getName()])) {
        $prop->setValue($entita, $object[$prop->getName()]);
      }
    }

    $entita->__construct(); 

    return $entita;
  }

  public function trova($options)
  {
    $class = new \ReflectionClass($this);
    $tabella = '';
    
    if ($this->tabella != '') {
      $tabella = $this->tabella;
    } else {
      $tabella = strtolower($class->getShortName());
    }

    $result = [];
    $whereSection = '';

    if (is_array($options)) {
      $counter = 1;
      foreach ($options as $key => $value) {
        if($counter <= 1) {
          $whereSection = "WHERE $key = '$value' ";
        } else {
          $whereSection .= " AND $key = '$value' ";
        }
        $counter++;
      }
    } elseif (is_string($options)) { 
      $whereSection = " $options ";
    } else {
      throw new \Exception('Parametro errato nella variabile OPTIONS');
    }

    $finalQuery = "SELECT * from $tabella $whereSection";

    $stmt = $this->db->prepare($finalQuery);
    $stmt->execute();
    $raw = $stmt->fetchAll();


    foreach ($raw as $rawRow) {
      $result[] = $this->plasma($rawRow);
    }

    return count($result) > 1 ? $result : $result[0];

  }

  public function cancella($options = '')
  {
    $class = new \ReflectionClass($this);
    $tabella = '';
    
    if ($this->tabella != '') {
      $tabella = $this->tabella;
    } else {
      $tabella = strtolower($class->getShortName());
    }

    $result = [];
    $whereSection = '';

    if($options != '') {
      if (is_array($options)) {
        $counter = 1;
        foreach ($options as $key => $value) {
          if($counter <= 1) {
            $whereSection = "WHERE $key = '$value' ";
          } else {
            $whereSection .= " AND $key = '$value' ";
          }
          $counter++;
        }
      } elseif (is_string($options) AND !is_numeric($options)) { 
        $whereSection = " $options ";
      } elseif(is_numeric($options) AND !is_string($options)) {
        $whereSection = " WHERE id = $options ";
      } else {
        throw new \Exception('Parametro errato nella variabile OPTIONS');
      }

      $finalQuery = "DELETE * from $tabella $whereSection";

      if(isset($_GET['test'])) {
        die($finalQuery);
      }

      $stmt = $this->db->prepare($finalQuery);
      $stmt->execute();

    } else {
      return "Nessun elemento specificato per l'eliminazione";
    }
  }
}

