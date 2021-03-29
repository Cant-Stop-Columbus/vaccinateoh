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
        CRUD::column('address')
            ->limit(255)
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::addColumn([
            'name' => 'available',
            'label' => 'Next Appointment',
            'orderable' => true,
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->leftJoin(
                    \DB::raw('(SELECT min(availability_time) as available,location_id
                        FROM availabilities
                        WHERE availability_time >= DATE(NOW()) AND doses > 0 AND deleted_at IS NULL
                        GROUP BY location_id
                    ) AS a'), 'locations.id', '=', 'a.location_id')
                ->orderBy('a.available', $columnDirection);
            }
        ]);
        CRUD::column('county')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('bookinglink')
            ->limit(255)
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('phone')->label('Booking Phone');
        CRUD::column('provider_url')->label('Provider URL')
            ->limit(255)
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('provider_phone');
        CRUD::column('alternate_addresses')->limit(255);
            /*
        CRUD::column('address2')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('city')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('state');
        */
        CRUD::column('zip')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('serves')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('vaccinesoffered')
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::column('siteinstructions')
            ->limit(255)
            ->searchLogic([self::class, 'searchCaseInsensitive']);
        CRUD::addColumn([
            'name' => 'appointmentTypes',
            'entity' => 'appointmentTypes',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addColumn([
            'name' => 'location_type_id',
            'entity' => 'type',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addColumn([
            'name' => 'location_type_id',
            'entity' => 'type',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addColumn([
            'name' => 'location_source_id',
            'entity' => 'locationSource',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addColumn([
            'name' => 'data_update_method_id',
            'entity' => 'dataUpdateMethod',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addColumn([
            'name' => 'collector_user_id',
            'entity' => 'collectorUser',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::column('daysopen');
        CRUD::column('latitude');
        CRUD::column('longitude');
        CRUD::column('created_at');
        CRUD::column('updated_at');

        CRUD::addButtonFromModelFunction('line', 'availability', 'buttonUpdateAvailability', 'beginning');
        CRUD::enableExportButtons();

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
        CRUD::field('bookinglink')->label('Booking URL');
        CRUD::field('phone')->label('Booking Phone');
        CRUD::field('provider_url')->label('Provider URL');
        CRUD::field('provider_phone');
        CRUD::field('address')
            ->type('textarea');
        CRUD::field('alternate_addresses')
            ->label('Alternate Addresses (first line of additional addresses for matching purposes only; one per line)')
            ->type('textarea');
        //CRUD::field('address2');
        //CRUD::field('city');
        //CRUD::field('state');
        CRUD::field('zip');
        CRUD::field('serves');
        CRUD::field('vaccinesoffered');
        CRUD::field('siteinstructions');
        CRUD::field('daysopen');
        CRUD::field('county');
        CRUD::field('latitude');
        CRUD::field('longitude');
        CRUD::field('system_type');
        CRUD::addField([
            'name' => 'appointmentTypes',
            'entity' => 'appointmentTypes',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addField([
            'name' => 'location_type_id',
            'entity' => 'type',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addField([
            'name' => 'location_type_id',
            'entity' => 'type',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addField([
            'name' => 'location_source_id',
            'entity' => 'locationSource',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addField([
            'name' => 'data_update_method_id',
            'entity' => 'dataUpdateMethod',
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addField([
            'name' => 'collector_user_id',
            'entity' => 'collectorUser',
            'type' => 'relationship',
            'attribute' => 'name',
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
        return $this->setupCreateOperation();
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
