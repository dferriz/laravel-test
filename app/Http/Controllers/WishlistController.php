<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(){


//        /** @var User $user */
//        $user = $this->getUser();
//        if (!$user instanceof User)
//            return $this->redirectToRoute('main_index');
//        $wishProducts = $user->getWishProducts();
//
//        return $this->render('wishlist/index.html.twig', [
//            'wishProducts' => $wishProducts->getIterator()
//        ]);
        $wishProducts = [];
        return view('wishlist.index', ['wishProducts'=>$wishProducts]);
    }
}
