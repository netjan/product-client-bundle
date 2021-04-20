<?php

namespace NetJan\ProductClientBundle\Controller;

use NetJan\ProductClientBundle\Entity\Product;
use NetJan\ProductClientBundle\Form\ProductType;
use NetJan\ProductClientBundle\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $filters = [
            'stock' => $this->normalizeStock($request->get('stock')),
        ];

        $products = $this->productRepository->getList($filters);
        if (is_string($products)) {
            $this->addFlash("error", $products);
        }

        return $this->render('@NetJanProductClient/product/index.html.twig', [
            'products' => $products,
            'stock' => $filters['stock'],
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
            $result = $this->productRepository->save($product);

            if (false === $result['error']) {
                $this->addFlash('success', 'Dane zapisane.');

                return $this->redirectToRoute('netjan_product_index');
            } else {
                foreach ($result['messages'] as $message) {
                    $this->addFlash("error", $message);
                }
            }
        }

        return $this->render('@NetJanProductClient/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     */
    public function show($id): Response
    {
        $product = $this->getProduct($id);

        return $this->render('@NetJanProductClient/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit($id, Request $request): Response
    {
        $product = $this->getProduct($id);
        $orginalProduct = clone $product;

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->productRepository->save($product, $orginalProduct);

            if (false === $result['error']) {
                $this->addFlash('success', 'Dane zapisane.');

                return $this->redirectToRoute('netjan_product_index');
            } else {
                foreach ($result['messages'] as $message) {
                    $this->addFlash("error", $message);
                }
            }
        }

        return $this->render('@NetJanProductClient/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"POST"})
     */
    public function delete($id, Request $request): Response
    {
        $product = $this->getProduct($id);

        if ($this->isCsrfTokenValid('netjan_product_delete' . $product->getId(), $request->request->get('_token'))) {
            $result = $this->productRepository->remove($product);

            if (false === $result['error']) {
                $this->addFlash('success', 'Dane usunięte.');
            } else {
                foreach ($result['messages'] as $message) {
                    $this->addFlash("error", $message);
                }
            }
        }

        return $this->redirectToRoute('netjan_product_index');
    }

    private function getProduct($id): ?Product
    {
        $product = $this->productRepository->find($id);

        if (null === $product) {
            throw new NotFoundHttpException();
        }

        if (is_string($product)) {
            throw new NotFoundHttpException($product);
        }

        return $product;
    }

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
