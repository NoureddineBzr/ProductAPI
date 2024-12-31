<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api')]
class ProductController extends AbstractController
{
    #[Route('/products', methods: ['GET'])]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();
        return $this->json($products);
    }

    #[Route('/products', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice($data['price']);
        $product->setQuantity($data['quantity']);
        $product->setSelected($data['selected']);
        $product->setAvailable($data['available']);

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json($product);
    }

    #[Route('/products/{id}', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        return $this->json($product);
    }

    #[Route('/products/{id}', methods: ['PUT'])]
    public function update(Request $request, Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $product->setName($data['name']);
        $product->setPrice($data['price']);
        $product->setQuantity($data['quantity']);
        $product->setSelected($data['selected']);
        $product->setAvailable($data['available']);

        $entityManager->flush();

        return $this->json($product);
    }
    #[Route('/products/{id}', methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    // Soufiane //

    // Get selected products
    #[Route('/products/selected', methods: ['GET'])]
    public function getSelectedProducts(ProductRepository $productRepository): JsonResponse
    {
        $selectedProducts = $productRepository->findBy(['selected' => true]);
        return $this->json($selectedProducts);
    }

    // Get available products
    #[Route('/products/available', methods: ['GET'])]
    public function getAvailableProducts(ProductRepository $productRepository): JsonResponse
    {
        $availableProducts = $productRepository->findBy(['available' => true]);
        return $this->json($availableProducts);
    }

    // Search products by name
    #[Route('/products/search', methods: ['GET'])]
    public function search(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $keyword = $request->query->get('name', '');

        // Search for products where the name contains the keyword
        $products = $productRepository->createQueryBuilder('p')
            ->where('p.name LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->getQuery()
            ->getResult();

        if (empty($products)) {
            return new JsonResponse(['message' => 'No products found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($products);
    }
}