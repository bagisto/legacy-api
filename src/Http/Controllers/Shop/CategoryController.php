<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\API\Http\Resources\Catalog\Category as CategoryResource;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  Webkul\Category\Repositories\CategoryRepository  $categoryRepository
     * @return void
     */
    public function __construct(protected CategoryRepository $categoryRepository)
    {
        $this->middleware('validateAPIHeader');
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return CategoryResource::collection(
            $this->categoryRepository->getVisibleCategoryTree(request()->input('parent_id'))
        );
    }
}
