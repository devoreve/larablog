# Avec Laravel la vie est belle

## Mise en place de la base de données de notre site web

### Création de la base de données et configuration

1. Créer la base de données {nom utilisateur}_laravel dans phpmyadmin (en UTF8MB4 unicode ci)
2. Configurer le fichier .env avec les informations de connexion à la base de données

### Création des tables avec les migrations

* Taper les commandes suivantes 
    ```
    php artisan make:migration CreatePostsTable
    php artisan migrate
    ```
* Modifer la classe CreatePostsTable (dans database/migrations/)
    ```php
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title', 100);
        $table->text('content');
        // Création de la clé étrangère user_id
        $table->unsignedBigInteger('user_id');
        $table->foreign('user_id')->references('id')->on('users');
        // Création des champs created_at et updated_at
        $table->timestamps();
    });
    ```
* Taper les commandes suivantes
    ```
    php artisan migrate:rollback 
    php artisan migrate
    ```

### Remplissage de la base de données

seeder : Classe qui va insérer des données dans la base de données
factory : Classe qui génère des données fictives

* Création du "seeder" des utilisateurs
    ```
    php artisan make:seeder UserSeeder
    ```
* Créer un utilisateur dans le fichier *UserSeeder.php* créé
    ```php
    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;
    
    class UserSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
            DB::table('users')->insert([
                'email' => 'admin@larablog.dev',
                'name' => 'admin',
                'password' => bcrypt('admin')
            ]);
        }
    }
    ```
* Création du modèle des articles
    ```
    php artisan make:model Post
    ```
* Création du "seeder" des articles
    ```
    php artisan make:seeder PostSeeder
    ```
* Création de la "factory" des articles
    ```
    php artisan make:factory PostFactory
    ```

    ```php
    public function definition()
    {
        return [
            'title' => $this->faker->words(3, true),
            'content' => $this->faker->paragraphs(5, true),
            'user_id' => 1
        ];
    }
    ```
* Mettre à jour la méthode *run* du fichier *DatabaseSeeder.php* dans le dossier *database/seeders*
    ```php
    public function run()
    {
        $this->call([
            UserSeeder::class,
            PostSeeder::class
        ]);
    }
    ```
* Modifier le fichier *PostSeeder.php* dans le dossier *database/seeders*
    ```php
    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\Post;
    
    class PostSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
            // Création de 100 articles fictifs
            Post::factory()->count(100)->create();
        }
    }
    ```
* Lancer la commande suivante
    ```
    php artisan db:seed
    ```

    **NB**: Cette commande appelle la méthode run du *DatabaseSeeder*. Il est aussi possible d'appeler indépendamment chaque seeder en précisant le nom de la classe (faites l'un ou l'autre, pas les 2).
    ```
    php artisan db:seed --class=UserSeeder
    php artisan db:seed --class=PostSeeder
    ```

Pour tout faire d'un coup (annuler les migrations, relancer les migrations et remplir la base de données), taper la commande suivante

```
php artisan migrate:refresh --seed
```

Raccourci des commandes suivantes :
```
php artisan migrate:refresh
php artisan db:seed
```

## Création du blog

* Récupérer les fichiers layout.blade.php et home.blade.php (à mettre dans le dossier ressources)
* Copier/coller le contenu de Post.php (dans le dossier app/Models)
* Créer le contrôleur DefaultController à l'aide de la commande
    ```
    php artisan make:controller DefaultController
    ```
* Créer la route pour la page d'accueil (dans le fichier routes/web.php)
    ```php
    Route::get('/', [App\Http\Controllers\DefaultController::class, 'home'])->name('home');
    ```
* Créer la méthode home qui renvoie les 5 derniers articles
    ```php
    public function home()
    {
        // Récupère les 5 articles les plus récents avec les informations de l'utilisateur
        $posts = Post::latest()->take(5)->with('user')->get();
        
        return view('home', [
            'posts' => $posts    
        ]);
    }
    ```

### Affichage de la liste des articles

* Créer le contrôleur PostController à l'aide de la commande artisan
* Créer la route /posts qui appelle le PostController et la méthode index
* Dans la méthode index, récupérer la liste de tous les articles du plus récent au plus ancien (voir code de la méthode home du DefaultController)
* Afficher tous ces articles dans un template posts/index.blade.php
    ```php
    public function index()
    {
        ...
        
        return view('posts.index', [
            'posts' => ...
        ]);
    }
    ```

### Pagination

Changer dans la méthode *index* du *PostController* la ligne
```php
$posts = Post::with('user')->latest()->get();
```
par
```php
$posts = Post::with('user')->latest()->paginate(10);
```

Modifier le fichier *app/Providers/AppServiceProvider.php* comme ceci :
```php
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    ...
    
    public function boot()
    {
        Paginator::useBootstrap();
    }
}
```

Modifier le fichier *ressources/views/posts/index.blade.php* comme ceci :

```
<h1>Liste des articles du blog</h1>
    
<nav>
    {{ $posts->links() }}
</nav>
```

### Navigation layout

Changer dans le fichier *ressources/views/layout.blade.php* les url des liens de navigation

```
<a href="{{ route('home') }}">Accueil</a>
...
<a href="{{ route('posts.index') }}">Blog</a>
```

### Mise en place d'un slug

#### Modifier la base de données

Changer la migration des posts et rajouter un champ *slug* de type string.

```php
$table->string('slug')->unique();
```

Dans la "factory" qui génère des articles fictifs, créer un slug à partir du titre.

```php
$title = $this->faker->words(3, true);
$slug = Str::slug($title, '-');

return [
    'title' => $title,
    'slug' => $slug,
    ...
];
```

Ajouter le "use" dans ce fichier pour utiliser la classe Str

```php
use Illuminate\Support\Str;
```

Relancer les migrations et les seeders

```
php artisan migrate:refresh --seed
```

#### Afficher l'article correspondant au slug

* Créer la route /posts/{slug} qui appelle la méthode show du PostController
* Dans la méthode show, récupérer le slug dans l'url puis récupérer l'article correspondant
* Afficher l'article récupéré dans un template *posts/show.blade.php*
* Afficher une erreur 404 si l'article n'est pas trouvé

#### Créer un lien sur les titres vers l'article

Dans le fichier *posts/index.blade.php*, ajouter un lien sur les titres pour que l'on puisse être redirigé vers la page de l'article en question.

```
<a href="{{ route('posts.show', ['slug' => $post->slug]) }}">...</a>
```

### Afficher un formulaire de création d'article

* Créer une route /posts/create (à mettre avant /posts/{slug}) qui appelle la méthode *create* du *PostController*
* Créer la méthode *create* dans le *PostController*
* Retourner la vue posts.create
* Créer le template *posts/create.blade.php*
* Créer un formulaire en POST qui va renvoyer les données vers la route /posts/store

### Enregistrer l'article

Créer une route /posts/store en POST qui appelle la méthode *store* du *PostController*
    ```php
    Route::post('/posts', ...)->name('posts.store');
    ```

Dans le formulaire, rajouter la directive blade qui permet de contrôler la faille CSRF.

```php
<form ...>
@csrf
...
</form>
```

#### Validation des données du formulaire

* Dans la méthode store du contrôleur, récupérer les données contenues dans la requête.
    ```php
    public function store(Request $request)
    {
        
    }
    ```
* Mettre en place les règles de validation de la requête
    ```php
    $request->validate([
        'title' => ...,
        'content' => ...
    ]);
    ```
* Afficher les erreurs dans le formulaire
    ```
    @if($errors->any())
        <aside class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </aside>
    @endif
    ```

#### Afficher les erreurs en français

* Changer la langue (la locale) de l'application dans config/app.php (passer 'en' à 'fr')
* Changer la timezone par "Europe/Paris" dans config/app.php
* Dupliquer le dossier ressources/lang/en et le renommer en fr
* Copier/coller le fichier ressources/lang/fr/validation.php du live-50

#### Enregistrement de l'article

* Dans la méthode *store* du *PostController*, après la validation du formulaire, enregistrer l'article à l'aide de l'orm.
    ```php
    $post = new Post();
    $post->title = $request->input('title');
    $post->content = $request->input('content');
    $post->user_id = 1
    $post->save();
    ```

* Rediriger vers l'accueil
    ```php
    return redirect()->route('home');
    ```

### Inscription

#### Affichage du formulaire

* Créer une classe *UserController* à l'aide de l'outil artisan dans le terminal
* Créer une route en GET /register qui appelle la méthode *register* du *UserController* (la route portera le nom register)
* Créer la méthode register qui va renvoyer la vue users.register
* Créer le template *users/register.blade.php* dans le dossier *ressources/views*
* Créer un formulaire d'authentification (name, email, password, password confirmation)
* Ne pas oublier la directive blade pour la faille csrf (@csrf à mettre dans le formulaire)
* Le formulaire sera en post et envoie les données vers une route *signup*

#### Enregistrement de l'utilisateur

* Créer une route /users en POST qui appelle la méthode *signup* du *UserController* (la route portera le nom signup)
* Créer la méthode *signup* dans le *UserController*
* Cette méthode va récupérer la requête et vérifier les données du formulaire
  * name : obligatoire
  * email : obligatoire et de type email
  * password : obligatoire, minimum 6 caractères, correspond au password_confirmation
  * password_confirmation : obligatoire
* Enregistrer l'utilisateur à l'aide l'ORM
* Rediriger vers la page de connexion (la route login)

### Connexion

#### Affichage du formulaire

* Créer la route /users/login qui appelle la méthode *login* du *UserController* (la route portera le nom login)
* Créer la méthode *login* qui renvoie la vue users.login
* Créer le template *users/login.blade.php*
* Créer un formulaire de connexion (email, password)
* Ne pas oublier la directive blade pour la faille csrf (@csrf à mettre dans le formulaire)
* Le formulaire sera en post et envoie les données vers une route *signin*

#### Authentifier l'utilisateur

* Créer une route /users en post qui appelle la méthode *signin* du *UserController*
* Créer la méthode *signin* qui contient le code suivant
    ```php
    public function signin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/');
        }
        
        return back()->withErrors([
            'credentials' => 'Les identifiants ne correspondent pas'
        ]);
    }
    ```
* Vous pouvez récupérer l'erreur d'authentification dans le template *login.blade.php* avec la variable $errors
    ```
    @if($errors->has('credentials'))
        <aside class="alert alert-danger">
            {{ $errors->first('credentials') }}
        </aside>
    @endif
    ```

### Déconnexion

* Créer une route /users/logout en get qui appelle la méthode *logout* du *UserController*
* La méthode *logout* contient le code suivant
    ```php
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
    ```

### Mise à jour de la navigation sur le layout

Modifier le fichier *layout.blade.php* pour avoir une navigation en fonction de si on est connecté ou non.

```
@auth
    <li class="nav-item">
        <a href="{{ route('logout') }}" class="nav-link">Déconnexion</a>
    </li>
@endauth
@guest
    <li class="nav-item">
        <a href="{{ route('login') }}" class="nav-link">Connexion</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('register') }}" class="nav-link">Inscription</a>
    </li>
@endguest
```

### Middleware

Dans la classe *PostController*, créer un constructeur qui va ajouter les middleware sur les actions non autorisées si on n'est pas connecté.

```php
public function __construct()
{
    // Middleware d'authentification
    // Avant d'appeler la méthode du contrôleur il vérifie qu'il peut le faire
    // Pour appeler les méthodes create et store il faut être authentifié
    // Sinon on est redirigé vers la page de login
    $this->middleware('auth')->only(['create', 'store']);
}
```

Dans la classe *UserController*, créer un constructeur qui va ajouter les middleware sur les actions non autorisées si on est connecté.

```php
public function __construct()
{
    $this->middleware('guest')->except('logout');
}
```

Modifier le fichier *app/Middleware/RedirectIfAuthenticated.php*
```php
if (Auth::guard($guard)->check()) {
    return redirect()->route('home');
}
```

### Commentaires

#### Création de la table

* Créer une nouvelle migration CreateCommentsTable
    ```
    php artisan make:migration CreateCommentsTable
    ```
* Créer des champs pseudo (string), content (text), post_id (unsignedBigInteger)
* Migrer
    ```
    php artisan migrate
    ```

#### Création du modèle

Rappel : Un modèle c'est une classe qui fait le lien avec la table dans la base de données.

* Taper la commande
    ```
    php artisan make:model Comment
    ```
* Mettre en place la relation entre les articles et les commentaires

#### Création du formulaire d'ajout de commentaire

* Sur le template posts/show.blade.php, créer un formulaire d'ajout de commentaires
* 3 champs à créer (post_id, pseudo, content)
* Ne pas oublier la directive blade @csrf
* Le formulaire sera en post et enverra sur une route qui s'appelle posts.comments

#### Ajout du commentaire en base de données

* Créer la route /posts/{id}/comments en post qui appelle la méthode *store* du *CommentController*
* Ajouter le commentaire en base de données à l'aide du modèle
* Rediriger vers le détail de l'article (posts.show)

#### Affichage de tous les commentaires

* Dans la méthode *show* du *PostController* on récupère la liste des commentaires de l'article
    ```php
    $comments = $post->comments()->latest()->get();
    ```
* Dans le template on affiche tous les commentaires

### Catégories

#### Création des tables

* Créer la table des catégories *categories*
* Créer la table *category_post*
    ```php
    Schema::create('category_post', function (Blueprint $table) {
        $table->primary(['post_id', 'category_id']);
        $table->unsignedBigInteger('post_id');
        $table->unsignedBigInteger('category_id');
        $table->foreign('post_id')->on('posts')->references('id');
        $table->foreign('category_id')->on('categories')->references('id');
    });
    ```
* Lancer les migrations
* Créer un seeder pour les catégories
    ```
    php artisan make:seeder CategorySeeder
    ```
* Ajouter des catégories
    ```php
    public function run()
    {
        DB::table('categories')->insert([
            [
                'name' => 'RPG',
            ],
            [
                'name' => 'Action'    
            ],
            [
                'name' => 'Tactique'    
            ],
            [
                'name' => 'FPS'
            ],
            [
                'name' => 'Simulation'    
            ]
        ]);
    }
    ```
    Ne pas oublier le use tout en haut
    ```php
    use Illuminate\Support\Facades\DB;
    ```
* Lancer le seeder des catégories
    ```
    php artisan db:seed --class=CategorySeeder
    ```

#### Création du modèle

* Créer le modèle Category
* Mettre en place la relation dans les modèles
    ```php
    // Dans le modèle Category
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
    
    // Dans le modèle Post
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    ```

#### Modification du Formulaire d'ajout de l'article

* Dans la méthode *create* du *PostController*, récupérer la liste des catégories
* Dans le formulaire du template, créer un champ select (avec l'attribut multiple) puis parcourir la liste des catégories pour créer les options

#### Enregistrement des catégories

* Dans la méthode *store* du *PostController*, ajouter les catégories au post créé
    ```php
    $post->attach([1, 3, 4]);   // Mettre les numéros qui viennent du formulaire
    ```

#### Afficher les catégories dans le détail de l'article

* Dans la méthode *show* du *PostController*, récupérer la liste des catégories du post
    ```php
    $categories = $post->categories;
    ```
* Afficher ces catégories dans le template du détail de l'article

### Option "se souvenir de moi"

* Créer un champ de type checkbox sur le formulaire de connexion
    ```
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="remember_me" id="remember-me">
        <label class="form-check-label" for="remember-me">
            Se souvenir de moi
        </label>
    </div>
    ```
* Modifier la méthode *signin* du *UserController*
    ```php
    // Rajouter ces lignes
    $rememberMe = $request->input('remember_me');
    $rememberMe = $rememberMe === 'on';
    
    // Modifier la connexion
    if (Auth::attempt($credentials, $rememberMe)) {
        $request->session()->regenerate();

        return redirect()->intended('/');
    }
    ```

### Liste des utilisateurs du blog

#### Créer 100 utilisateurs fictifs

* Dans le UserSeeder, rajouter cette ligne pour appeler la factory (à mettre après le premier utilisateur)
    ```php
    User::factory()->count(100)->create();
    ```
* Relancer les migrations
    ```
    php artisan migrate:refresh --seed
    ```

#### Afficher les utilisateurs

* Créer la route /users en GET qui appelle la méthode *index* du *UserController*
* Dans la méthode *index* on récupère la liste des utilisateurs du site
* On les affiche dans un template users/index.blade.php
* Les utilisateurs seront tous affichés dans un tableau html

#### Recherche dynamique

##### Partie serveur

* Créer un champ de recherche sur le formulaire
* Créer une route /ajax/users en get qui appelle la méthode *search* du *UserController*
* Cette méthode récupère la liste des utilisateurs correspondant à un filtre (sur le nom de l'utilisateur)
    ```php
    // Récupère la liste des utilisateurs contenant le texte toto
    User::where('nom du champ', 'like', '%toto%')->get();  
    ```
* Le filtre sera récupéré depuis la chaîne de requête
    ```php
    public function search(Request $request)
    {
        $request->input('search');  // Equivalent laravel de $_GET['search']
    }
    ```
* Afficher ces utilisateurs dans une vue partielle (ne contenant que l'affichage du corps du tableau)
* Créer le template partials/users/index.blade.php
* Tester tout ça avant de faire la partie client : aller sur l'url /ajax/users puis sur ajax/users?search=adm

##### Partie client
* Créer un fichier *main.js* dans le dossier *public/js* (le créer s'il n'existe pas)
* Désactiver la soumission du formulaire
    ```js
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
    });
    ```
* Mettre en place un événement lorsque l'on appuie sur une touche dans le champ de recherche
* Envoyer une requête ajax vers l'url /ajax/users avec la valeur du champ de recherche
* Récupérer le contenu renvoyé par le serveur et remplacer le corps du tableau actuel par celui reçu
    ```js
    tbody.innerHTML = results;
    ```

### Forcer le https

Modifier le fichier app/Providers/AppServiceProvider
```php
public function boot()
{
    Paginator::useBootstrap();
    URL::forceScheme('https');
}
```

Ne pas oublier le use tout en haut du fichier
```php
use Illuminate\Support\Facades\URL;
```