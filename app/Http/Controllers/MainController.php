<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    /**
     * Shows a paginated product list view.
     *
     * @param Request $request
     * @param int $page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, $page = 1) {

        $orderByOptions = [
            'price' => ['field'=>'price', 'order' => 'asc'],
            '!price' => ['field' => 'price', 'order' => 'desc'],
            'title' => ['field'=>'name', 'order'=>'asc'],
            '!title' => ['field'=>'name', 'order'=>'desc']
        ];

        $orderByKey = $request->get('order');

        if ( !$orderByKey || !array_key_exists($orderByKey, $orderByOptions) ) {
            $orderByKey = 'price';
        }

        $orderBy = $orderByOptions[$orderByKey];
//        $paginator = $productRepository->findAllPaginated($page, $orderBy['field'], $orderBy['order']);
        $paginator = [];
        return view('main.index', ['paginator'=>$paginator]);
    }
}
