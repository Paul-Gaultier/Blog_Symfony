<?php

namespace App\Controller;


use App\Entity\Article;
use App\Form\ArticleType;
use App\Form\EditArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ArticleController extends AbstractController
{

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager){

        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/article", name="create_article")
     * @param Request $request
     * @return Response
     */
    public function createArticle(Request $request, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        
        if($form->isSubmitted() && $form->isValid()){

            $article = $form->getData();

            # Association de l'article au user : setOwner()
            //

            # Association de l'article à la category : setOwner()
            //

            $article->setCreatedAt(new \DateTime());

            # Coder ici la logique pour uploader la photo
            //

            // On récupère le fichier du formulaire grâce à getData(). Cela nous retourne un objet dt type Uploadedfile
        
            $file = $form->get('picture')->getData();//On crée une variable $file qui va récuperer les infos sur l'image à partir de getData() soumis à Article

            //dd($file);

            if($file) {// Si $file isset (existe) alors: condition de vérifcation du fichier $file


                //générer une contrainte d'upload. On déclare un array avec deux valeurs de type string qui sont
                //les MimeType autorisés. (le MineType est le type du fichier/propre à tous les fichiers en informatique. il s'agit de la plus petite unité -> voir sur mozilla developper sur google )
                $allowedMimeType = ['image/jpeg', 'image/png'];

                /*La fonction in_array viens check si la valeur exist dans un array
                Ici on va vérifier la présence de getMineType, le getMineType s'assure de la bonne extension du fichier
                */

                //Elle permet de comparer deux valeurs (2 arguments attendus ici)
                if(in_array($file->getMimeType(),$allowedMimeType)){//in_array(la valeur, le tableau concernée)


                    

                        //Nous allons construire le nouveau nom du fichier :

                        //Dans la variabla $originalFilename on va stocker le nom du fichier.
                        //On utilise une fonction native à symfo le pathinfo()
                        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);


                        #Récupération de l'extension
                        $extension = '.'. $file->guessExtension();//On utilise la concatenation pour ajouter un point '.'


                        #Assainissement du nom grâce au slugger fourni par symfony pour la construction du nouveau nom (=enlever tt les caractères parasite/inutiles/indésirables)
                        //$safeFilename = $slugger->slug($article->getTitle()); //Si on veut lui donner le nom du titre de l'article
                        $safeFilename = $slugger->slug($originalFilename);

                        #Construction du nouveau nom
                        $newFilename = $safeFilename. '_' .uniqid() . $extension; //la fonction unidid()permet de donner un identifiant unique au nouvel nom du fichier

                        
                        // On utilise un try() catch() lorqu'on appelle une méthode qui lance une erreur
                        try {
                            
                            /*On lance la méthode move qui utilise en paramètre le Uploads_dir défini dans service.yaml
                            La methode move() de UploadedFile permet de pouvoir déplacer le fichier dans son dossier de destination.
                            Le dossier de destination a été parametré dans son service.yaml


                            /!\ ATTENTION :
                                    La méthode move() lance une erreur de type FileException.
                                    On attrape cette erreur dans le catch(FileException $exception)
                            */
                            $file->move($this->getParameter('uploads_dir'), $newFilename);
    
                            //On set la nouvelle valeur (nom du fichier) de la propriété picture de notre objet Article.
                            $article->setPicture($newFilename);
    
                        } catch (FileException $exception) {


                            // code à exécuter si une erreur est attrapée

                            
    
                        }

                    // }else {//Si ce n'est pas le bon type de fiché uploadé, alors on affiche un msg et on rédirige

                    //     //addFlash permet de stocker un msg qui est aussitot vidé après utilisation
                    //     $this->addFlash('warning', 'Les types de fichiers autorisés sont : .jpeg / .png');
                    //     return $this->redirectToRoute('create_article');
                    //}
                }

            }

            $this->entityManager->persist($article);//Ajoute les données en bdd
            $this->entityManager->flush();//vide la boite EntityManager

            $this->addFlash('success','Article ajouter!');

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/form_article.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/modifier/article/{id}", name="edit_article")
     * @param Article $article
     * @param Request $request
     * @return Response
     */
    public function editArticle(Article $article, Request $request): Response
    {
        # Supprimer le edit form et utiliser ArticleType (configurer les options) : pas besoin de dupliquer un form
        $form = $this->createForm(EditArticleType::class, $article)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            # Créer une nouvelle propriété dans l'entité : setUpdatedAt()

            $this->entityManager->persist($article);
            $this->entityManager->flush();

        }

        return $this->render('article/edit_article.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/voir/article/{id}", name="show_article")
     * @param Article $singleArticle
     * @return Response
     */
    public function showArticle(Article $singleArticle): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($singleArticle->getId());

        return $this->render('article/show_article.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * @Route("/admin/supprimer/article/{id}", name="delete_article")
     * @param Article $article
     * @return Response
     */
    public function deleteArticle(Article $article): Response
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();

        $this->addFlash('success','Article supprimé !');

        return $this->redirectToRoute('dashboard');
    }
}
