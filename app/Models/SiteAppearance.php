<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Une seule ligne attendue : surcharge vitrine + thème e-mails (prioritaire sur config / .env).
 */
class SiteAppearance extends Model
{
    protected $fillable = [
        'favicon_path',
        'site_logo_path',
        'mail_layout_partial',
        'quesako_config',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'mail_layout_partial' => 'array',
            'quesako_config' => 'array',
        ];
    }

    public static function settings(): self
    {
        $row = static::query()->first();
        if ($row !== null) {
            return $row;
        }

        return static::query()->create([
            'favicon_path' => null,
            'site_logo_path' => null,
            'mail_layout_partial' => null,
            'quesako_config' => static::defaultQuesakoConfig(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultQuesakoConfig(): array
    {
        return [
            'tabs' => [
                ['id' => 'tab-about', 'slug' => 'about', 'label' => 'Quesako', 'enabled' => true, 'order' => 1],
                ['id' => 'tab-services', 'slug' => 'services', 'label' => 'Services', 'enabled' => true, 'order' => 2],
                ['id' => 'tab-timeline', 'slug' => 'timeline', 'label' => 'Parcours', 'enabled' => true, 'order' => 3],
            ],
            'modulesByTab' => [
                'about' => [
                    [
                        'id' => 'hero-about',
                        'type' => 'hero',
                        'enabled' => true,
                        'order' => 1,
                        'props' => [
                            'headline' => 'Je transforme les idees en experiences web claires.',
                            'subheadline' => 'Developpement web full-stack, architecture propre et execution solide.',
                            'animationVariant' => 'fade-up',
                        ],
                    ],
                    [
                        'id' => 'about-main',
                        'type' => 'about',
                        'enabled' => true,
                        'order' => 2,
                        'props' => [
                            'title' => 'Qui je suis',
                            'body' => 'Je conçois des produits web fiables, rapides et evolutifs, avec une approche pragmatique orientee resultat.',
                        ],
                    ],
                ],
                'services' => [
                    [
                        'id' => 'hero-services',
                        'type' => 'hero',
                        'enabled' => true,
                        'order' => 1,
                        'props' => [
                            'headline' => 'Services sur mesure, du concept a la mise en ligne.',
                            'subheadline' => 'Produit, code, performance et maintenance continue.',
                            'animationVariant' => 'fade-up',
                        ],
                    ],
                    [
                        'id' => 'services-main',
                        'type' => 'services',
                        'enabled' => true,
                        'order' => 2,
                        'props' => [
                            'title' => 'Ce que je fais',
                            'items' => [
                                'Applications web metier',
                                'Architecture backend & APIs',
                                'Design d interfaces epurees',
                            ],
                        ],
                    ],
                ],
                'timeline' => [
                    [
                        'id' => 'hero-timeline',
                        'type' => 'hero',
                        'enabled' => true,
                        'order' => 1,
                        'props' => [
                            'headline' => 'Un parcours axe execution et amelioration continue.',
                            'subheadline' => 'Chaque etape nourrit une approche plus simple, plus robuste.',
                            'animationVariant' => 'fade-up',
                        ],
                    ],
                    [
                        'id' => 'timeline-main',
                        'type' => 'timeline',
                        'enabled' => true,
                        'order' => 2,
                        'props' => [
                            'title' => 'Parcours',
                            'steps' => [
                                ['label' => 'Fondations', 'text' => 'Apprentissage des bases solides backend/frontend.'],
                                ['label' => 'Production', 'text' => 'Livraison de solutions concretes pour des besoins reels.'],
                                ['label' => 'Evolution', 'text' => 'Optimisation continue, automation et qualite.'],
                            ],
                        ],
                    ],
                ],
            ],
            'settings' => [
                'defaultTabSlug' => 'about',
                'seoTitle' => 'Quesako - BrightShell',
                'seoDescription' => 'Qui je suis, ce que je fais et comment je travaille.',
            ],
        ];
    }
}
