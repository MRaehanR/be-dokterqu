<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Article\ArticlePostRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;

/**
 * Class ArticlePostCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ArticlePostCrudController extends CrudController
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
        $this->crud->setModel(\App\Models\ArticlePost::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/article-post');
        $this->crud->setEntityNameStrings('article post', 'article posts');
        $this->crud->setCreateView('admin.article_post.create');
        $this->crud->denyAccess('show');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {

        $this->crud->addColumns([
            [
                'label' => 'Title',
                'type' => 'text',
                'name' => 'title',
            ],
            [
                'label' => 'Thumbnail',
                'type' => 'image',
                'name' => 'thumbnail',
            ],
            [
                'label' => 'Category',
                'type' => 'select',
                'entity' => 'category',
                'attribute' => 'name',
                'model' => 'App\Models\ArticleCategory',
            ],
            [
                'label' => 'Upload By',
                'type' => 'select',
                'name' => 'author',
                'attribute' => 'name',
                // 'entity' => 'uploadBy',
                // 'model' => 'App\Models\User',
            ],
            // [
            //     'label' => 'Slug',
            //     'type' => 'text',
            //     'name' => 'slug',
            //     'prefix' => env('APP_URL')."/article/",
            // ],
            [
                'label' => 'Status',
                'name' => 'status',
                'type' => 'enum',
            ],
            [
                'label' => 'Updated At',
                'name' => 'updated_at',
                'type' => 'datetime',
            ]
        ]);

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
        $this->crud->setValidation(ArticlePostRequest::class);
        Widget::add()->type('script')->content('js/forms/slug.js');

        $this->crud->addField([
                'label' => 'Upload By',
                'type' => 'hidden',
                'name' => 'upload_by',
                'value' => backpack_user()->id,
                // 'entity' => 'uploadBy',
                // 'attribute' => 'name',
                // 'model' => 'App\Models\User',
                // 'attributes' => [
                //     'show' => false,
                // ]
                // 'visible' => false,
        ]);
        $this->crud->field('category_id');
        $this->crud->addField([
            'label' => 'Thumbnail',
            'name' => 'thumbnail',
            'type' => 'upload',
            'upload' => true,
            'disk' => 'public',
        ]);
        $this->crud->field('title');
        $this->crud->field('slug');
        $this->crud->addfield([
            'label' => 'Status',
            'name' => 'status',
            'type' => 'enum',
        ]);

        $this->crud->addField([
            'label' => 'Body',
            'name' => 'body',
            'type' => 'summernote',
        ]);

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
        // $this->setupCreateOperation();
        Widget::add()->type('script')->content('js/forms/slug.js');

        $this->crud->addField([
                'label' => 'Upload By',
                'type' => 'hidden',
                'name' => 'upload_by',
                'value' => backpack_user()->id,
                // 'entity' => 'uploadBy',
                // 'attribute' => 'name',
                // 'model' => 'App\Models\User',
                // 'attributes' => [
                //     'show' => false,
                // ]
                // 'visible' => false,
        ]);
        $this->crud->field('category_id');
        $this->crud->addField([
            'label' => 'Thumbnail',
            'name' => 'thumbnail',
            'type' => 'upload',
            'upload' => true,
            'disk' => 'public',
        ]);
        $this->crud->field('title');
        $this->crud->field('slug');
        $this->crud->addfield([
            'label' => 'Status',
            'name' => 'status',
            'type' => 'enum',
        ]);

        $this->crud->addField([
            'label' => 'Body',
            'name' => 'body',
            'type' => 'summernote',
        ]);
    }
}
