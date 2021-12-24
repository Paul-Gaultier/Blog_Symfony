<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Category;
use App\Form\EditArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;



//Etant donnée qu'on a commenté l'accès control pour l'admin dans le security.yaml
//On fait l'annotation de sécuroté ici avec le @IsGranted (Attention si plusieurs dashboards, laisser la sécurité admin dans security.yaml)

//Lorsqu'on met l'annotation just en dessus de la classe c'est pour qu'elle soit prise en compte dans toutes les fonctions présentent dans la classe
/**
 * @IsGranted("ROLE_ADMIN")
 */
class DashboardController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/dashboard", name="dashboard")
     * @return Response
     */
    public function dashboard(): Response
    {
        $articles = $this->entityManager->getRepository(Article::class)->findAll();

        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->render('dashboard/dashboard.html.twig',
            [
                'articles' => $articles,
                'users' => $users,
                'categories'=>$categories
            ]
        );
    }

    //Si nous voulons restrainte l'action de supprimer à un super_admin qui serait dans la bdd
    //On va indqiquer dans l'anotation juste avant la fonction le @IsGranted("ROLE_SUPER_ADMIN)

    /**
     * @IsGranted ("ROLE_SUPER_ADMIN")
     * @Route("/admin/supprimer/user/{id}", name="delete_user")
     * @param User $user
     * @return Response
     */
    public function deleteUser(User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success','Utilisateur supprimé !');

        return $this->redirectToRoute('dashboard');
    }

}
