<?php

declare(strict_types=1);

namespace NetJan\ProductClientBundle\Controller;

use NetJan\ProductClientBundle\Entity\Product;
use NetJan\ProductClientBundle\Exception\ExceptionInterface;
use NetJan\ProductClientBundle\Filter\ProductFilter;
use NetJan\ProductClientBundle\Form\ProductType;
use NetJan\ProductClientBundle\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $filter = new ProductFilter();
        $filter->stock = $this->normalizeStock($request->get('stock'));

        $products = [];
        try {
            $products = $this->productRepository->list($filter);
        } catch (ExceptionInterface $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('@NetJanProductClient/product/index.html.twig', [
            'products' => $products,
            'filter' => $filter,
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $response = $this->save($product)) {
                return $response;
            }
        }

        return $this->renderForm('@NetJanProductClient/product/new.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id<\d+>}", name="product_show", methods={"GET"})
     */
    public function show(int $id): Response
    {
        $product = $this->getProduct($id);

        return $this->render('@NetJanProductClient/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id<\d+>}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(int $id, Request $request): Response
    {
        $product = $this->getProduct($id);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $response = $this->save($product)) {
                return $response;
            }
        }

        return $this->renderForm('@NetJanProductClient/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id<\d+>}", name="product_delete", methods={"POST"})
     */
    public function delete(int $id, Request $request): Response
    {
        $product = $this->getProduct($id);

        if ($this->isCsrfTokenValid('netjan_product_delete'.$product->getId(), $request->request->get('_token'))) {
            try {
                $this->productRepository->remove($product);

                $this->addFlash('success', 'Dane usunięte.');
            } catch (ExceptionInterface $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('netjan_product_index', [], Response::HTTP_SEE_OTHER);
    }

    private function save(Product $product): ?Response
    {
        try {
            $this->productRepository->save($product);
            $this->addFlash('success', 'Dane zapisane.');

            return $this->redirectToRoute('netjan_product_index', [], Response::HTTP_SEE_OTHER);
        } catch (ExceptionInterface $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return null;
    }

    private function getProduct(int $id): Product
    {
        try {
            $product = $this->productRepository->find($id);
        } catch (ExceptionInterface $e) {
            throw new ServiceUnavailableHttpException($e->getMessage());
        }

        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Produkt "%d" nie znaleziono.', $id));
        }

        return $product;
    }

    /**
     * @param mixed $stock
     */
    private function normalizeStock($stock): ?bool
    {
        if (\in_array($stock, [true, 'true', '1'], true)) {
            return true;
        }

        if (\in_array($stock, [false, 'false', '0'], true)) {
            return false;
        }

        return null;
    }
}
