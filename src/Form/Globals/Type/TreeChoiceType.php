<?php

namespace App\Form\Globals\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormView,
    Symfony\Component\Form\FormInterface,
    Symfony\Component\OptionsResolver\Options,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Doctrine\Common\Persistence\ObjectManager;

use App\Form\Globals\Type\ChoiceList\ORMSortedQueryBuilderLoader,
    App\Library\Globals\TreeEntitySorter,
    App\Library\NavigationElementInterface;

/**
 * Class TreeChoiceType
 * @package App\Form\Globals\Type
 */
class TreeChoiceType extends AbstractType
{
    /**
     * @var TreeEntitySorter
     */
    protected $treeEntitySorter;

    /**
     * Constructor
     *
     * @param TreeEntitySorter $treeEntityOrderer
     */
    public function __construct(TreeEntitySorter $treeEntitySorter)
    {
        $this->treeEntitySorter = $treeEntitySorter;
    }

    /**
     * Get Parent
     *
     * This field type is based on EntityType
     *
     * @return null|string
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'tree_choice';
    }

    /**
     * {@inheritdoc}
     *
     * The EntityChoiceList has been extended to sort choices
     *
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // Set a default query_builder so our custom Loader is always used
        $queryBuilder = function (Options $options) {
            return $options['em']->getRepository($options['class'])->createQueryBuilder('e')->select('e');
        };

        // Set a custom Loader that will sort the entities if the option is set
        $type = $this;

        $loader = function (Options $options) use ($type, $queryBuilder) {
            return $type->getLoader($options['em'], $options['query_builder'], $options['class'], $options['automatic_sorting']);
        };

        // Replace the default options with these new ones
        $resolver->setDefaults([
            'query_builder' => $queryBuilder,
            'loader' => $loader
        ]);

        // Add some custom default options
        $defaults = [
            'automatic_sorting' => true,
            'add_select_all'    => true
        ];

        $resolver->setDefaults($defaults);
    }

    /**
     * Return the default loader object
     *
     * @param ObjectManager $manager
     * @param $queryBuilder
     * @param $class
     * @param $automaticSorting
     *
     * @return ORMSortedQueryBuilderLoader
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class, $automaticSorting)
    {
        return new ORMSortedQueryBuilderLoader(
            $queryBuilder,
            $this->treeEntitySorter,
            $manager,
            $class,
            $automaticSorting
        );
    }

    /**
     * Add a level property for all children to the main FormView
     *
     * In case of a multiple widget not expanded, buildViewBottomUp won't be called because
     * all different values are in the same widget
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @throws \Exception
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Get the entities
        $entities = $view->vars['choices'];

        $levels = array();
        foreach ($entities as $id => $entity) {

            if (!$entity->data instanceof NavigationElementInterface) {
                throw new \Exception('Tree Choice elements must extend the NavigationElementInterface.');
            }

            $levels['level_id_' . $id] = $entity->data->getLevel();
        }

        $view->vars['levels'] = $levels;

        // For the Generic Label
        $view->vars['withColon'] = true;

        // Add the parameter to the view
        if ($options['add_select_all']) {
            $view->vars['add_select_all'] = true;
        }
    }

    /**
     * Add a level property to all children FormView
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @throws \Exception
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        // Get the entity list
        $entityChoiceList = $view->vars['choices'];

        // For all children (FormView instances)
        // For example : 'sections' (root FormView) contains many checkboxes (children FormView)
        foreach ($view->children as $key => $childrenView) {
            if (array_key_exists($key, $entityChoiceList)) {
                $entity = $entityChoiceList[$key];

                if (!$entity->data instanceof NavigationElementInterface) {
                    throw new \Exception('Tree Choice elements must extend the NavigationElementInterface.');
                }

                // Set the level on the child FormView, a checkbox for example
                $childrenView->vars['level'] = $entity->data->getLevel();
            }
        }
    }

}