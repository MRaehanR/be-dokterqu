<?php

namespace App\Http\Controllers\Admin;

use App\Mail\DoctorVerificationRejected;
use App\Models\DoctorInfo;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

use function PHPUnit\Framework\isNull;

/**
 * Class DoctorVerificationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DoctorVerificationCrudController extends CrudController
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
        $this->crud->setModel(\App\Models\User::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/doctor-verification');
        $this->crud->setEntityNameStrings('doctor verification', 'doctor verifications');
        $this->crud->allowAccess(['create']);
        $this->crud->setListView('admin.user.list');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->setFromDb(); // columns

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - $this->crud->column('price')->type('number');
         * - $this->crud->addColumn(['name' => 'price', 'type' => 'number']); 
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
        $this->crud->setFromDb(); // fields

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - $this->crud->field('price')->type('number');
         * - $this->crud->addField(['name' => 'price', 'type' => 'number'])); 
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

    public function updateStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'user_id' => 'required',
                'status' => 'required|in:open,accepted,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $doctor = DoctorInfo::where('id', $request->id)->first();
            $user = User::where('id', $request->user_id)->first();

            if (!isset($doctor)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Doctor info Not Found',
                ], Response::HTTP_NOT_FOUND);
            }

            $doctor->update([
                'status' => $request->status,
            ]);

            if($request->status == "accepted"){
                $user->update([
                    'active' => 1,
                ]);
                event(new Registered($user));
            } else if($request->status == "rejected"){
                $user->update([
                    'active' => 0,
                ]);
                Mail::to($user->email)->send(new DoctorVerificationRejected($user->name));
            } else {
                $user->update([
                    'active' => 0,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Doctor Info status has been updated',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
