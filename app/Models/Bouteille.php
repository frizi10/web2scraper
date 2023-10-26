<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bouteille extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'nom',
        'prix',
        'format',
        'pays',
        'region',
        'cepage',
        'lienProduit',
        'srcImage',
        'srcsetImage',
        'designation',
        'degre',
        'tauxSucre',
        'couleur',
        'producteur',
        'agentPromotion',
        'produitQuebec',
        'type',
        'millesime'
    

      
    ];
}
