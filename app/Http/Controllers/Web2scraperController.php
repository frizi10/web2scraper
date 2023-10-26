<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Bouteille;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Web2scraperController extends Controller
{
    public function scrapeData(Request $request)
    {
        set_time_limit(0);
        $client = new Client();

        $pageMax = 100;
        for ($page = 1; $page <= $pageMax; $page++) {
            $url = "https://www.saq.com/fr/produits/vin?p={$page}&product_list_limit=24&product_list_order=name_asc";
            $response = $client->request('GET', $url);
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            // récupérer les autres informations comme le prix, le typeVin, le format, etc
            $crawler->filter('li.product-item')->each(function (Crawler $node) use ($client) {
                $nom = $node->filter('.product.name.product-item-name')->text();
                $code = $node->filter('.saq-code span:last-child')->text();
                $lienProduit =  $node->filter('a.product.photo.product-item-photo')->attr('href');
                $srcImage =  $node->filter('img.product-image-photo')->attr('src');
                $srcsetImage =  $node->filter('img.product-image-photo')->attr('srcset');

                // le prix du bouteille ;
                $prixText = $node->filter('.price')->text();
                // Supprimer les caractères non numériques (virgules, espaces, etc.)
                $prixText = preg_replace('/[^0-9,.]/', '', $prixText);
                // Si le format est "29.95", on n'a pas besoin de remplacer les espaces
                if (strpos($prixText, '.') !== false) {
                    $prix = (float) $prixText;
                } else {
                    // Convertir la chaîne en nombre à virgule flottante
                    $prix = (float) str_replace(',', '.', str_replace(' ', '', $prixText));
                }
                // Formater le nombre avec deux chiffres après la virgule
                $prix = number_format($prix, 2, '.', '');

                $identitiy =  $node->filter('.product.product-item-identity-format')->text();
                $identitiyArray = explode('|', $identitiy);
                $type = trim($identitiyArray[0]);
                $format = trim($identitiyArray[1]);

                $lesMatches = [];
                preg_match('/\b\d{4}\b/', $nom, $lesMatches);

                $millesime = null;
                if (isset($lesMatches[0])) {
                    $millesime = $lesMatches[0];
                }

                $detailResponse = $client->request('GET', $lienProduit);

                if ($detailResponse->getStatusCode() === 200) {
                    $detailHtml = $detailResponse->getBody()->getContents();
                    $detailCrawler = new Crawler($detailHtml);
                    $listAttributs = $detailCrawler->filter('.list-attributs li');

                    $informations = [];

                    $listAttributs->each(function (Crawler $li) use (&$informations) {
                        $span = trim($li->filter('span')->text());
                        $strong = trim($li->filter('strong')->text());
                        $informations[$span] = $strong;
                    });

                    $pays = isset($informations['Pays']) ? trim($informations['Pays']) : null;
                    $region = isset($informations['Région']) ? trim($informations['Région']) : null;
                    $cepage = isset($informations['Cépage']) ? trim($informations['Cépage']) : null;
                    $designation = isset($informations['Désignation réglementée']) ? trim($informations['Désignation réglementée']) : 'non';
                    $degre = isset($informations['Degré d\'alcool']) ? trim($informations['Degré d\'alcool']) : null;
                    $tauxSucre = isset($informations['Taux de sucre']) ? trim($informations['Taux de sucre']) : null;
                    $couleur = isset($informations['Couleur']) ? trim($informations['Couleur']) : null;
                    $producteur = isset($informations['Producteur']) ? trim($informations['Producteur']) : null;
                    $agentPromotion = isset($informations['Agent promotionnel']) ? $informations['Agent promotionnel'] : null;
                    $produitQuebec = isset($informations['Produit du Québec']) ? $informations['Produit du Québec'] : null;

                    // Mettre à jour ou créer une nouvelle bouteille
                    Bouteille::updateOrCreate(
                        ['id' => $code],
                        [
                            'nom' => $nom,
                            'prix' => $prix,
                            'pays' => $pays,
                            'format' => $format,
                            'type' => $type,
                            'lienProduit' => $lienProduit,
                            'srcImage' => $srcImage,
                            'srcsetImage' => $srcsetImage,
                            'designation' => $designation,
                            'degre' => $degre,
                            'tauxSucre' => $tauxSucre,
                            'region' => $region,
                            'cepage' => $cepage,
                            'couleur' => $couleur,
                            'millesime' => $millesime,
                            'producteur' => $producteur,
                            'agentPromotion' => $agentPromotion,
                            'produitQuebec' => $produitQuebec,
                        ]
                    );

                    echo $produitQuebec. '<br>';
                } else {
                    echo 'La requête a échoué avec le code : ' . $detailResponse->getStatusCode();
                    // Gérer l'échec de la requête, par exemple, en enregistrant un message d'erreur.
                }
            });
        }
    }
}
