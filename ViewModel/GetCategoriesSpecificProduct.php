<?php
namespace Perspective\GetCategoriesSpecificProduct\ViewModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\SessionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class GetCategoriesSpecificProduct implements ArgumentInterface
{

    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    private SessionFactory $catalogSessionFactory;
    private ProductRepositoryInterface $productRepository;
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        SessionFactory $catalogSessionFactory,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->catalogSessionFactory = $catalogSessionFactory;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getCurrentProduct(): ProductInterface
    {
        $productID = $this->catalogSessionFactory->create()->getData('last_viewed_product_id');
        return $this->productRepository->getById($productID);
    }

    /**
     * @return CategoryInterface|null
     * @throws NoSuchEntityException
     */
    public function getCategory(): ?CategoryInterface
    {
        $categoryID = $this->catalogSessionFactory->create()->getData('last_viewed_category_id');
        $category = $categoryID ? $this->categoryRepository->get($categoryID) : null;
        return $category && in_array($category->getId(), $this->getCurrentProduct()->getCategoryIds())
            ? $category : null;
    }

    /**
     * @return CategoryInterface[]
     */
    public function getCategories(): array
    {
        try {
            if ($product = $this->getCurrentProduct()) {
                $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $product->getCategoryIds(), 'in')->create();
                $categories = $this->productRepository->getList($searchCriteria);
                return $categories->getItems();
            }
        } catch (\Exception $exception) {

        }
        return [];
    }

}

