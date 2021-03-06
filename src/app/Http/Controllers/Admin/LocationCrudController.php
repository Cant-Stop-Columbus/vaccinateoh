<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LocationRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class LocationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LocationCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Location::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/location');
        CRUD::setEntityNameStrings('location', 'locations');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('name')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::addColumn([
            'name' => 'available',
            'label' => 'Next Appointment',
            'orderable' => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->leftJoin(
                    \DB::raw('(SELECT min(availability_time) as available,location_id
                        FROM availabilities
                        WHERE availability_time > NOW()
                        GROUP BY location_id
                    ) AS a'), 'locations.id', '=', 'a.location_id')
                ->orderBy('a.available', $columnDirection);
            }
        ]);
        CRUD::column('bookinglink')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('address')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('address2')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('city')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('state');
        CRUD::column('zip')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('county')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('serves')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('vaccinesoffered')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('siteinstructions')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('daysopen');
        CRUD::column('latitude');
        CRUD::column('longitude');
        CRUD::column('created_at');
        CRUD::column('updated_at');

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
        CRUD::setValidation(LocationRequest::class);

        CRUD::field('name');
        CRUD::field('bookinglink');
        CRUD::field('address');
        CRUD::field('address2');
        CRUD::field('city');
        CRUD::field('state');
        CRUD::field('zip');
        CRUD::field('serves');
        CRUD::field('vaccinesoffered');
        CRUD::field('siteinstructions');
        CRUD::field('daysopen');
        CRUD::field('county');
        CRUD::field('latitude');
        CRUD::field('longitude');

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

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     * @return void
     */
    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }

    /**
     * Helper function for a case-insensitive search columns on the list view
     *
     * @param QueryBuilder $query
     * @param array $column
     * @param string $searchTerm
     * @return void
     */
    static function searchCaseInsensitive($query, $column, $searchTerm) {
        $query->orWhere($column['name'], 'ILIKE', '%'.$searchTerm.'%');
    }
}
