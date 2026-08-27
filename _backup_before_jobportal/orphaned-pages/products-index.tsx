import { Head } from '@inertiajs/react';
import products from '@/routes/products';

export default function Product() {
    return (
        <>
            <Head title="Product" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
              <h1>Hello there product</h1>
            </div>
        </>
    );
}

Product.layout = {
    breadcrumbs: [
        {
            title: 'Product',
            href: products.index(),
        },
    ],
};
