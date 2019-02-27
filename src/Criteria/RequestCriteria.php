<?php
namespace Aruberuto\Repository\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Aruberuto\Repository\Contracts\CriteriaInterface;
use Aruberuto\Repository\Contracts\RepositoryInterface;

/**
 * Class RequestCriteria
 * @package Aruberuto\Repository\Criteria
 */
class RequestCriteria implements CriteriaInterface
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /**
     * Apply criteria in query repository
     *
     * @param         Builder|Model     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     * @throws \Exception
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $fieldsSearchable = $repository->getFieldsSearchable();

        $constraint = $this->request->get(config('repository.criteria.params.constraint', 'constraint'), []);

        $datesearch = $this->request->get(config('repository.criteria.params.datesearch', 'datesearch'), []);

        $search = $this->request->get(config('repository.criteria.params.search', 'search'), null);
        $searchFields = $this->request->get(config('repository.criteria.params.searchFields', 'searchFields'), null);
        $filter = $this->request->get(config('repository.criteria.params.filter', 'filter'), null);
        $orderBy = $this->request->get(config('repository.criteria.params.orderBy', 'orderBy'), null);
        $sortedBy = $this->request->get(config('repository.criteria.params.sortedBy', 'sortedBy'), 'asc');
        $with = $this->request->get(config('repository.criteria.params.with', 'with'), null);
        $searchJoin = $this->request->get(config('repository.criteria.params.searchJoin', 'searchJoin'), null);
        $sortedBy = !empty($sortedBy) ? $sortedBy : 'asc';


        // Search by dates. Examples
        // [
        //     'created_at' => '2019-02-27;2019-03-31', //between
        //     'start_date' => '2019-05-01:>=', // starting date
        //     'end_date' => '2019-05-10:<=', // Ending date
        // ];

        if(is_array($datesearch) && count($datesearch)) {
            foreach($datesearch as $field => $value) {

                $comparator = $this->getDateSearchComparator($value);
                $value = str_before($value, ':' . $comparator);
                $explode = explode(';', $value);

                $start_date = null;
                $end_date = null;
                if(count($explode) > 1) {
                    $between = true;
                    $start_date = $explode[0];
                    $end_date = $explode[1];
                } else {
                    $between = false;
                }

                $relation = null;
                if(stripos($field, '.')) {
                    $explodeField = explode('.', $field);
                    $field = array_pop($explodeField);
                    $relation = implode('.', $explodeField);
                }

                if (!is_null($value)) {
                    if(!is_null($relation)) {
                        $model->whereHas($relation, function($query) use($field,$comparator,$value, $start_date, $end_date, $between) {

                            if($between) {
                                $query->whereDate($field, '>=', $start_date)->whereDate($field, '<=', $end_date);
                            } else {
                                $query->whereDate($field, $comparator, $value);
                            }
                        });
                    } else {

                        if($between) {
                            $model->whereDate($field, '>=', $start_date)->whereDate($field, '<=', $end_date)->get();
                        } else {
                            $model->whereDate($field, $comparator, $value)->get();
                        }
                    }
                }
            }
        }


        // Constraint: similar to search but with restriction in a singular field
        if(is_array($constraint) && count($constraint)) {
            foreach($constraint as $field => $value) {
                $internalOperator = $this->getConstraintInternalOperator($value);
                $value = str_before($value, ':' . $internalOperator);
                $explode = explode(';', $value);

                $relation = null;
                if(stripos($field, '.')) {
                    $explodeField = explode('.', $field);
                    $field = array_pop($explodeField);
                    $relation = implode('.', $explodeField);
                }

                if (!is_null($value)) {
                    if(!is_null($relation)) {
                        $model->whereHas($relation, function($query) use($field,$internalOperator,$value, $explode) {
                            $query->where(function ($query) use($field, $internalOperator, $explode) {
                                foreach($explode as $val) {
                                    if($internalOperator === 'like') {
                                        $val = '%'. $val . '%';
                                    }
                                    $query->orWhere($field, $internalOperator,  $val )->get();

                                }
                            });
                        });
                    } else {

                        $model->where(function ($query) use($field, $internalOperator, $explode) {
                            foreach($explode as $val) {
                                if($internalOperator === 'like') {
                                    $val = '%'. $val . '%';
                                }
                                $query->orWhere($field, $internalOperator,  $val )->get();

                            }
                        });
                    }
                }
            }
        }
        if ($search && is_array($fieldsSearchable) && count($fieldsSearchable)) {

            $searchFields = is_array($searchFields) || is_null($searchFields) ? $searchFields : explode(';', $searchFields);
            $fields = $this->parserFieldsSearch($fieldsSearchable, $searchFields);
            $isFirstField = true;
            $searchData = $this->parserSearchData($search);
            $search = $this->parserSearchValue($search);
            $modelForceAndWhere = strtolower($searchJoin) === 'and';

            $model = $model->where(function ($query) use ($fields, $search, $searchData, $isFirstField, $modelForceAndWhere) {
                /** @var Builder $query */

                foreach ($fields as $field => $condition) {

                    if (is_numeric($field)) {
                        $field = $condition;
                        $condition = "=";
                    }

                    $value = null;

                    $condition = trim(strtolower($condition));

                    if (isset($searchData[$field])) {
                        $value = ($condition == "like" || $condition == "ilike") ? "%{$searchData[$field]}%" : $searchData[$field];
                    } else {
                        if (!is_null($search)) {
                            $value = ($condition == "like" || $condition == "ilike") ? "%{$search}%" : $search;
                        }
                    }

                    $relation = null;
                    if(stripos($field, '.')) {
                        $explode = explode('.', $field);
                        $field = array_pop($explode);
                        $relation = implode('.', $explode);
                    }
                    $modelTableName = $query->getModel()->getTable();
                    if ( $isFirstField || $modelForceAndWhere ) {
                        if (!is_null($value)) {
                            if(!is_null($relation)) {
                                $query->whereHas($relation, function($query) use($field,$condition,$value) {
                                    $query->where($field,$condition,$value);
                                });
                            } else {
                                $query->where($modelTableName.'.'.$field,$condition,$value);
                            }
                            $isFirstField = false;
                        }
                    } else {
                        if (!is_null($value)) {
                            if(!is_null($relation)) {
                                $query->orWhereHas($relation, function($query) use($field,$condition,$value) {
                                    $query->where($field,$condition,$value);
                                });
                            } else {
                                $query->orWhere($modelTableName.'.'.$field, $condition, $value);
                            }
                        }
                    }
                }
            });
        }

        if (isset($orderBy) && !empty($orderBy)) {
            $split = explode('|', $orderBy);
            if(count($split) > 1) {
                /*
                 * ex.
                 * products|description -> join products on current_table.product_id = products.id order by description
                 *
                 * products:custom_id|products.description -> join products on current_table.custom_id = products.id order
                 * by products.description (in case both tables have same column name)
                 */
                $table = $model->getModel()->getTable();
                $sortTable = $split[0];
                $sortColumn = $split[1];

                $split = explode(':', $sortTable);
                if(count($split) > 1) {
                    $sortTable = $split[0];
                    $keyName = $table.'.'.$split[1];
                } else {
                    /*
                     * If you do not define which column to use as a joining column on current table, it will
                     * use a singular of a join table appended with _id
                     *
                     * ex.
                     * products -> product_id
                     */
                    $prefix = str_singular($sortTable);
                    $keyName = $table.'.'.$prefix.'_id';
                }

                $model = $model
                    ->leftJoin($sortTable, $keyName, '=', $sortTable.'.id')
                    ->orderBy($sortColumn, $sortedBy)
                    ->addSelect($table.'.*');
            } else {
                $model = $model->orderBy($orderBy, $sortedBy);
            }
        }

        if (isset($filter) && !empty($filter)) {
            if (is_string($filter)) {
                $filter = explode(';', $filter);
            }

            $model = $model->select($filter);
        }

        if ($with) {
            $with = explode(';', $with);
            $model = $model->with($with);
        }

        return $model;
    }

    /**
     * @param $search
     *
     * @return array
     */
    protected function parserSearchData($search)
    {
        $searchData = [];

        if (stripos($search, ':')) {
            $fields = explode(';', $search);

            foreach ($fields as $row) {
                try {
                    list($field, $value) = explode(':', $row);
                    $searchData[$field] = $value;
                } catch (\Exception $e) {
                    //Surround offset error
                }
            }
        }

        return $searchData;
    }

    /**
     * @param $search
     *
     * @return null
     */
    protected function parserSearchValue($search)
    {

        if (stripos($search, ';') || stripos($search, ':')) {
            $values = explode(';', $search);
            foreach ($values as $value) {
                $s = explode(':', $value);
                if (count($s) == 1) {
                    return $s[0];
                }
            }

            return null;
        }

        return $search;
    }

    protected function getConstraintInternalOperator($value) {
        $explode = explode(':', $value);
        if(count($explode) > 1) {
            return end($explode);
        } else {
            return config('repository.criteria.params.constraintOperator', 'like');
        }
    }

    protected function getDateSearchComparator($value) {
        $explode = explode(':', $value);
        if(count($explode) > 1) {
            return end($explode);
        } else {
            return config('repository.criteria.params.dateSearchComparator', '>=');
        }
    }


    protected function parserFieldsSearch(array $fields = [], array $searchFields = null)
    {
        if (!is_null($searchFields) && count($searchFields)) {
            $acceptedConditions = config('repository.criteria.acceptedConditions', [
                '=',
                'like'
            ]);
            $originalFields = $fields;
            $fields = [];

            foreach ($searchFields as $index => $field) {
                $field_parts = explode(':', $field);
                $temporaryIndex = array_search($field_parts[0], $originalFields);

                if (count($field_parts) == 2) {
                    if (in_array($field_parts[1], $acceptedConditions)) {
                        unset($originalFields[$temporaryIndex]);
                        $field = $field_parts[0];
                        $condition = $field_parts[1];
                        $originalFields[$field] = $condition;
                        $searchFields[$index] = $field;
                    }
                }
            }

            foreach ($originalFields as $field => $condition) {
                if (is_numeric($field)) {
                    $field = $condition;
                    $condition = "=";
                }
                if (in_array($field, $searchFields)) {
                    $fields[$field] = $condition;
                }
            }

            if (count($fields) == 0) {
                throw new \Exception(trans('repository::criteria.fields_not_accepted', ['field' => implode(',', $searchFields)]));
            }

        }

        return $fields;
    }
}
