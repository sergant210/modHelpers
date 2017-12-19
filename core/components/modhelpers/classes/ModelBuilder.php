<?php

namespace modHelpers;

class ModelBuilder
{
    protected $table;
    public $columns = array();
    protected $indexes = array();
    protected $aggregates = array();
    protected $composites = array();
    /** @var ModelColumn $modelColumnClass*/
    protected $modelColumnClass;

    public function __construct($table)
    {
        $this->table = $table;
        $this->modelColumnClass = config('modhelpers_modelColumnClass', 'modHelpers\ModelColumn', true);
    }
    /**
     * Add a new char column to the model.
     *
     * @param  string  $name
     * @param  int  $precision
     * @return ModelColumn
     */
    public function char($name, $precision = 255)
    {
        $this->columns[] = $column = new $this->modelColumnClass('char', $name, compact('precision'));
        return $column;
    }

    /**
     * Add a new varchar column to the model.
     *
     * @param  string  $name
     * @param  int  $precision
     * @return ModelColumn
     */
    public function varchar($name, $precision = 255)
    {
        $this->columns[] = $column = new $this->modelColumnClass('varchar', $name, compact('precision'));
        return $column;
    }

    /**
     * Add a new text column to the model.
     *
     * @param  string  $name
     * @return ModelColumn
     */
    public function text($name)
    {
        $this->columns[] = $column = new $this->modelColumnClass('text', $name);
        return $column;
    }

    /**
     * Add a new medium text column to the model.
     *
     * @param  string  $name
     * @return ModelColumn
     */
    public function mediumText($name)
    {
        $this->columns[] = $column = new $this->modelColumnClass('mediumtext', $name);
        return $column;
    }

    /**
     * Add a new long text column to the model.
     *
     * @param  string  $name
     * @return ModelColumn
     */
    public function longText($name)
    {
        $this->columns[] = $column = new $this->modelColumnClass('longtext', $name);
        return $column;
    }

    /**
     * Add an unsigned integer column to the model.
     *
     * @param  string   $name
     * @param  int      $precision
     * @return ModelColumn
     */
    public function id($name = 'id', $precision = 10)
    {
        return $this->int($name, $precision, true);
    }
    /**
     * Add a new integer column to the model.
     *
     * @param  string   $name
     * @param  int      $precision
     * @param  bool     $unsigned
     * @return ModelColumn
     */
    public function int($name, $precision = 10, $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = 10;
        }
        $this->columns[] = $column = new $this->modelColumnClass('int', $name, compact('precision', 'unsigned'));
        return $column;
    }
    /**
     * Add a new tiny integer (1-byte) colum to the modeln.
     *
     * @param  string   $name
     * @param  int      $precision
     * @param  bool     $unsigned
     * @return ModelColumn
     */
    public function tinyInt($name, $precision = 3, $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = 3;
        }
        $this->columns[] = $column = new $this->modelColumnClass('tinyint', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new small integer (2-byte) column to the model.
     *
     * @param  string   $name
     * @param  int      $precision
     * @param  bool     $unsigned
     * @return ModelColumn
     */
    public function smallInt($name, $precision = 5, $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = 5;
        }
        $this->columns[] = $column = new $this->modelColumnClass('smallint', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new medium integer (3-byte) column to the model.
     *
     * @param  string   $name
     * @param  int      $precision
     * @param  bool     $unsigned
     * @return ModelColumn
     */
    public function mediumInt($name, $precision = 8, $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = 8;
        }
        $this->columns[] = $column = new $this->modelColumnClass('mediumint', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new big integer (8-byte) column to the model.
     *
     * @param  string   $name
     * @param  int      $precision
     * @param  bool     $unsigned
     * @return ModelColumn
     */
    public function bigInt($name, $precision = 20, $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = 20;
        }
        $this->columns[] = $column = new $this->modelColumnClass('bigint', $name, compact('precision', 'unsigned'));
        return $column;
    }
    /**
     * Add a new float column to the model.
     *
     * @param  string  $name
     * @param  string  $precision
     * @param  bool    $unsigned
     * @return ModelColumn
     */
    public function float($name, $precision = '12,2', $unsigned = false)
    {
        $this->columns[] = $column = new $this->modelColumnClass('float', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new double column to the model.
     *
     * @param  string   $name
     * @param  string   $precision
     * @param  bool     $unsigned
     * @return ModelColumn
     */
    public function double($name, $precision = '20,2', $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = '20,2';
        }
        $this->columns[] = $column = new $this->modelColumnClass('double', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new decimal column to the model.
     *
     * @param  string  $name
     * @param  string  $precision
     * @param  bool    $unsigned
     * @return ModelColumn
     */
    public function decimal($name, $precision = '12,2', $unsigned = false)
    {
        if (is_bool($precision)) {
            $unsigned = $precision;
            $precision = '12,2';
        }
        $this->columns[] = $column = new $this->modelColumnClass('decimal', $name, compact('precision', 'unsigned'));
        return $column;
    }

    /**
     * Add a new boolean column to the model.
     *
     * @param  string  $name
     * @return ModelColumn
     */
    public function boolean($name)
    {
        $this->columns[] = $column = new $this->modelColumnClass('boolean', $name, array('precision' => 1, 'unsigned' => true));
        return $column;
    }
    /**
     * Add a new array column to the model.
     *
     * @param  string  $name
     * @return ModelColumn
     */
    public function asArray($name)
    {
        $this->columns[] = $column = new $this->modelColumnClass('array', $name);
        return $column;
    }

    public function arr($name)
    {
        return $this->asArray($name);
    }
    /**
     * Add a new json column to the model.
     *
     * @param  string  $name
     * @return ModelColumn
     */
    public function json($name)
    {
        $this->columns[] = $column = new $this->modelColumnClass('json', $name);
        return $column;
    }
    /**
     * Add a new date column to the model.
     *
     * @param  string  $name
     * @return ModelColumn
     */
    public function date($name)
    {
        $this->columns[] = $column = new $this->modelColumnClass('date', $name);
        return $column;
    }

    /**
     * Add a new date-time column to the model.
     *
     * @param  string  $name
     * @return ModelColumn
     */
    public function dateTime($name)
    {
        $this->columns[] = $column = new $this->modelColumnClass('timestamp', $name);
        return $column;
    }
    /**
     * Add a new time column to the model.
     *
     * @param  string  $name
     * @return ModelColumn
     */
    public function time($name)
    {
        return $this->dateTime($name);
    }
    /**
     * Add a new timestamp column to the model.
     *
     * @param  string  $name
     * @return ModelColumn
     */
    public function timestamp($name)
    {
        return $this->dateTime($name);
    }

    public function aggregate($alias, $attributes)
    {
        $this->aggregates[$alias] = array(
            'class' => $attributes['class'],
            'local' => $attributes['local'],
            'foreign' => $attributes['foreign'],
            'cardinality' => $attributes['cardinality'],
            'owner' => $attributes['owner'],
        );
        return $this;
    }

    public function composite($alias, $attributes)
    {
        $this->composites[$alias] = array(
            'class' => $attributes['class'],
            'local' => $attributes['local'],
            'foreign' => $attributes['foreign'],
            'cardinality' => $attributes['cardinality'],
            'owner' => $attributes['owner'],
        );
        return $this;
    }

    public function output()
    {
        $output = $fields = $meta = $indexes = $indAliases = $aggregates = $composites = $aliases = $rules = array();
        /** @var ModelColumn $column */
        foreach ($this->columns as $column) {
            $fields[$column->name] = $column->getDefault();
            $meta[$column->name] = $column->attributes;
            // Indexes
            $iName = key($column->index);
            $iAlias = $column->index[$iName]['alias'];
            if (isset($indAliases[$iAlias])) {
                $indexes[$indAliases[$iAlias]]['columns'] = array_merge($indexes[$indAliases[$iAlias]]['columns'],$column->index[$iName]['columns']);
            } elseif (!empty($column->index)) {
                $indAliases[$iAlias] = $iName;
                $indexes[$iName] = $column->index[$iName];
            }
            // Aggregates and composites
            if (!empty($column->aggregate)) $aggregates = array_merge($aggregates, $column->aggregate);
            if (!empty($column->composite)) $composites = array_merge($composites, $column->composite);
            // Aliases
            if (!empty($column->alias)) $aliases = array_merge($aliases, $column->alias);
            // Validations
            if (!empty($column->rules)) $rules = array_merge($rules, $column->rules);
        }
        if (!empty($this->table)) $output['table'] = $this->table;
        $output['fields'] = $fields;
        $output['fieldMeta'] = $meta;
        if (!empty($aliases)) $output['fieldAliases'] = $aliases;

        if (!empty($indexes)) $output['indexes'] = $indexes;
        if (!empty($aggregates)) $output['aggregates'] = $aggregates;
        if (!empty($this->aggregates)) {
            if (is_null($output['aggregates'])) $output['aggregates'] = array();
            $output['aggregates'] = array_merge($output['aggregates'], $this->aggregates);
        }
        if (!empty($composites)) $output['composites'] = $composites;
        if (!empty($this->composites)) {
            if (is_null($output['composites'])) $output['composites'] = array();
            $output['composites'] = array_merge($output['composites'], $this->composites);
        }
        if (!empty($rules)) $output['validation']['rules'] = $rules;

        return $output;
    }
}