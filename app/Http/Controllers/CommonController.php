<?php

namespace App\Http\Controllers;

use App\Models\Proforma;
use App\Models\User;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_if(!in_array(auth()->user()->user_role, ['admin', 'caissier']), 503);
            return $next($request);
        });
    }
    public function modele_proforma()
    {
        $p =  proforma_dir();
        $models = [];
        foreach ($p as $k => $pa) {
            $id = $k + 1;
            $pro = file_get_contents("$pa/proforma");
            $img = encode("$pa/image.png");
            $models[] = (object) ['id' => $id, 'img' => $img];
        }
        return view('common.modele-proforma', compact('models'));
    }
    public function facture_proforma($id)
    {
        $p =  proforma_dir();
        if ($id > count($p)) {
            abort(404);
        }
        $proforma_id = $id;
        $path = $p[$id - 1];
        $pro = file_get_contents("$path/proforma");
        $modele = (object) ['id' => $id, 'data' => $pro];

        $req = Request::create(route('articles.index', ['filtre' => true]));
        $resp = app()->handle($req);
        $resp = json_decode($resp->getContent());
        $articles = $resp->data;
        $shop = shop();
        $email = User::where(['user_role' => 'admin', 'compte_id' => compte_id()])->first()->email;
        return view('common.facture-proforma', compact('modele', 'articles', 'shop', 'email', 'proforma_id'));
    }

    public function preview_proforma($id)
    {
        $p =  proforma_dir();
        if ($id > count($p)) {
            abort(404);
        }
        $path = $p[$id - 1];
        $pro = file_get_contents("$path/proforma");
        return build_proforma($pro)->proforma;
    }

    public function preview_proforma_html(Proforma $proforma)
    {
        return $proforma->html;
    }

    public function proforma()
    {
        return view('common.proforma');
    }

    public function proforma_show(Proforma $proforma)
    {
        return view('common.proforma-show', compact('proforma'));
    }

    function proforma_default()
    {
        $p =  proforma_dir();
        if (!count($p)) {
            return redirect(route('proforma'));
        }
        return redirect(route('proforma.facture', 1));
    }
}
