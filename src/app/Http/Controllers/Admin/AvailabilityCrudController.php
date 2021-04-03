<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AvailabilityRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use App\Models\User;

/**
 * Class AvailabilityCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AvailabilityCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Availability::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/availability');
        CRUD::setEntityNameStrings('availability', 'availabilities');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        
        CRUD::addColumn([
            'name' => 'location',
            'type' => 'relationship',
            'attribute' => 'name_address',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('location',function($query2) use($searchTerm) {
                    $query2->where('name', 'ILIKE', '%'.$searchTerm.'%');
                });
            },
            'orderable' => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->leftJoin('locations', 'locations.id', '=', 'availabilities.location_id')
                ->orderBy('locations.name', $columnDirection)
                ->orderBy('locations.address', $columnDirection);
            }
        ]);
        CRUD::column('doses');
        CRUD::column('availability_time');
        CRUD::addColumn([
            'name' => 'updated_by_user_id',
            'entity' => 'updated_by_user',
            'type' => 'relationship',
        ]);
        CRUD::addColumn([
            'name' => 'was_deleted',
            'label' => 'Deleted?',
            'type' => 'closure',
            'function' => function($entry) {
                return $entry->deleted_at ? 'Y' : '';
            },
        ]);

        CRUD::denyAccess('show');

        CRUD::addFilter([
            'type'  => 'simple',
            'name'  => 'manual',
            'label' => 'Show only manual updates'
          ],
          false,
          function($values) { // if the filter is active
              $this->crud->query = $this->crud->query->whereNotNull('updated_by_user_id');
          }
        );

        CRUD::addFilter([
            'type'  => 'simple',
            'name'  => 'trashed',
            'label' => 'Include old/deleted availability'
          ],
          false,
          function($values) { // if the filter is active
              $this->crud->query = $this->crud->query->withTrashed();
          }
        );

        CRUD::addFilter([
            'type'  => 'simple',
            'name'  => 'multiple_future',
            'label' => 'Has multiple future availabilities'
          ],
          false,
          function($values) { // if the filter is active
              $this->crud->query = $this->crud->query->whereHas('location', function($q) {
                  $q->has('futureAvailability', '>', 1);
              });
          }
        );

        $this->crud->addFilter([
            'name'  => 'updated_by_user_id',
            'type'  => 'select2',
            'label' => 'Updated By User'
        ], function () {
            return User::has('availabilities')->orderBy('name')->pluck('name', 'id')->prepend('-- None --',0)->toArray();
        }, function ($value) { // if the filter is active
            if($value == 0) {
                CRUD::addClause('whereNull', 'updated_by_user_id');
            } else {
                CRUD::addClause('where', 'updated_by_user_id', $value);
            }
        });

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(AvailabilityRequest::class);

        $request = CRUD::getRequest();

        CRUD::addField([
            'name' => 'location_id',
            'entity' => 'location',
            'type' => 'relationship',
            'attribute' => 'name_address',
            'default' => $request->get('location'),
            'allow_nulls' => true,
        ]);
        CRUD::field('availability_time')->default(\Carbon\Carbon::today()->addDays(3));
        CRUD::addField([
            'name' => 'doses',
            'label' => 'Doses (0 = no future availability)',
            'default' => 1,
        ]);
        CRUD::addField([
            'name' => 'brand',
            'type' => 'select_from_array',
            'options' => [
                'm' => 'Moderna',
                'p' => 'Pfizer',
                'j' => 'Jensen/Johnson & Johnson',
            ]
        ]);
        CRUD::addField([
            'name' => 'updated_by_user_id',
            'entity' => 'updated_by_user',
            'default' => \Auth::user()->id,
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addField([
            'name' => 'clear_existing',
            'label' => 'Clear existing future availability',
            'default' => true,
            'type' => 'checkbox',
        ]);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
