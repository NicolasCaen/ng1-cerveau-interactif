# Cerveau Interactif

Plugin WordPress qui affiche un cerveau SVG interactif configuré via le shortcode `[cerveau_interactif]`. Les contenus des zones sont éditables depuis l’admin, et plusieurs paramètres permettent d’ajuster l’apparence et les animations sans toucher au code.

## Installation

1. Copier le dossier `cerveau-interactif` dans `wp-content/plugins/`.
2. Activer le plugin « Cerveau Interactif » depuis le tableau de bord WordPress.
3. (Optionnel) Adapter les textes des zones dans *Réglages → Cerveau interactif*.

## Utilisation rapide

Insérer le shortcode dans n’importe quel contenu :

```text
[cerveau_interactif]
```

Le cerveau s’affichera avec les valeurs par défaut décrites ci-dessous.

## Paramètres du shortcode

| Attribut          | Type / Format acceptés                                         | Par défaut | Description |
|-------------------|----------------------------------------------------------------|------------|-------------|
| `opacity`         | `auto`, décimaux (`0.6`, `.6`), pourcentages (`60`, `60%`)     | `0.6`      | Opacité initiale des fonds (sauf méninges qui restent masquées). `auto` suit le comportement historique (toutes les zones visibles sauf méninges). |
| `stroke_color`    | Nom CSS, hex (`#ff00aa`), `rgb()`/`rgba()`                     | `red`      | Couleur du contour animé des boutons de zone. |
| `stroke_width`    | Nombre (avec `,` ou `.`), éventuellement suivi de `px`        | `40`       | Épaisseur maximale du contour pendant l’animation. |
| `stroke_opacity`  | Décimaux / pourcentages comme `opacity`                       | `0.7`      | Opacité maximale du contour pendant l’animation. |
| `stroke_duration` | Durée en secondes (`1.5`, `1,5s`, `2s`)                        | `1.5s`     | Durée d’un cycle d’animation du contour pulsé. |

### Règles de normalisation

- Les valeurs numériques acceptent les virgules ou points décimaux et peuvent être exprimées en pourcentage (`>1` est interprété comme `%`).
- Les valeurs sont automatiquement bornées entre `0` et `1` lorsque c’est pertinent (opacités).
- Les paramètres sont injectés comme variables CSS sur le conteneur `.cerveau-container`, ce qui garantit une application immédiate côté front.

## Exemples

```text
[cerveau_interactif opacity="auto"]
```
Affiche les fonds comme dans la version d’origine (toutes les zones visibles sauf méninges au repos).

```text
[cerveau_interactif opacity="0.4" stroke_color="#00ffcc" stroke_width="30"]
```
Opacité initiale réduite, contour turquoise plus fin.

```text
[cerveau_interactif stroke_opacity="80%" stroke_duration="2,2s"]
```
Animation plus lente avec contour plus opaque.

## Notes

- Les survols affichent toujours la zone correspondante, y compris les méninges, même si leur opacité initiale est nulle.
- Si un attribut est omis ou invalide, la valeur par défaut listée ci-dessus est utilisée.
- Les avertissements dans certains IDE concernant des fonctions WordPress (`shortcode_atts`, `get_option`, etc.) peuvent être ignorés : elles sont disponibles à l’exécution dans l’environnement WP.
