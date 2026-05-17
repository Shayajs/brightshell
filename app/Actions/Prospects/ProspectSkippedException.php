<?php

declare(strict_types=1);

namespace App\Actions\Prospects;

/**
 * Prospect ignoré avant enrichissement (hors cible PME, trop d'établissements, etc.).
 */
final class ProspectSkippedException extends \RuntimeException {}
