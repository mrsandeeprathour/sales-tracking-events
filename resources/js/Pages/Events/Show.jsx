import React, { useState, useEffect } from 'react';
import { Card, IndexTable, Page,Badge, Pagination, Thumbnail, Spinner } from '@shopify/polaris';
import { usePage, router } from '@inertiajs/react';

export default function Show() {
    const { props } = usePage();
    const [event, setEvent] = useState(null);
    const [eventSales, setEventSales] = useState([]);

    const [loading, setLoading] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const [productsPerPage] = useState(10); // Customize this value
    useEffect(() => {
        if (props.event) {
            setEvent(props.event);
            setEventSales(props.event_sales)
            setLoading(false); // Event loaded
        }
    }, [props.event]);

    // Ensure event is available before rendering
    if (loading) {
        return (
            <Page title="Event Details">
                <Spinner accessibilityLabel="Loading event details..." size="large" />
            </Page>
        );
    }

    // Pagination state
    const handlePageChange = (page) => {
        setCurrentPage(page);
    };

    // Get the current page products
    const salesArray = Array.isArray(eventSales) ? eventSales : Object.values(eventSales);

    // Paginate the sales
    const currentProducts = salesArray.slice(
        (currentPage - 1) * productsPerPage,
        currentPage * productsPerPage
    );
    // Calculate total products (for pagination)
    const totalProducts = eventSales?.length;

    // Handle product row click to navigate to product details
    const handleRowClick = (eventId, productId) => {
        console.log('eventId',eventId,productId)
        router.get(`/event-sale-details?event_id=${eventId}&product_id=${productId}`);
    };

    // Calculate total inventory for all variants of a product
    const calculateTotalInventory = (product) => {
        if (!product || !product.variants) return 0;
        return product.variants.reduce((total, variant) => total + (variant.total_inventory || 0), 0);
    };

    // Calculate sold inventory for all variants of a product
    const calculateSoldInventory = (product) => {
        if (!product || !product.variants) return 0;
        return product.variants.reduce((total, variant) => total + (variant.sold_inventory || 0), 0);
    };

    // Calculate in-hand inventory for all variants of a product
    const calculateInHandInventory = (product) => {
        if (!product || !product.variants) return 0;
        return product.variants.reduce((total, variant) => total + (variant.inhand_inventory || 0), 0);
    };

    // Default image if no image URL is available
    const getProductImage = (product) => {
        return product?.variants?.[0]?.images?.[0]?.src; // Use a placeholder if no image URL
    };

    return (
        <Page
            backAction={{
                content: 'Event Details',
                onAction: () => window.history.back(),
            }}
            title="Event Details"
        >
            <div>
                <Card title="Selected Products & Variants">
                    {currentProducts?.length > 0 ? (
                       <IndexTable
                       itemCount={currentProducts.length}
                       headings={[
                           { title: 'Image' },
                           { title: 'Title' },
                           { title: 'Status' },
                           { title: 'Total Inventory' },
                           { title: 'Sold Inventory' },
                           { title: 'In-Hand Inventory' }
                       ]}
                   >
                       {currentProducts?.map((product) => (
                           <IndexTable.Row
                               key={product?.id} // Use unique product ID as key
                               onClick={() => handleRowClick(product?.event_id, product?.id)}
                           >
                               <IndexTable.Cell>
                                   <Thumbnail
                                       source={getProductImage(product)}
                                       alt={product?.title || 'Product image'}
                                       size="small"
                                   />
                               </IndexTable.Cell>
                               <IndexTable.Cell>{product?.title}</IndexTable.Cell>
                               <IndexTable.Cell>{event.status === 'active' ? (
                                   <Badge progress="complete">Active</Badge>
                               ) : (
                                   <Badge progress="incomplete">Inactive</Badge>
                               )}</IndexTable.Cell>
                               <IndexTable.Cell>{calculateTotalInventory(product)}</IndexTable.Cell>
                               <IndexTable.Cell>{calculateSoldInventory(product)}</IndexTable.Cell>
                               <IndexTable.Cell>{calculateInHandInventory(product)}</IndexTable.Cell>
                           </IndexTable.Row>
                       ))}
                   </IndexTable>
                    ) : (
                        <p>No products available for this event.</p>
                    )}

                    {/* Pagination */}
                    <Pagination
                        hasPrevious={currentPage > 1}
                        onPrevious={() => handlePageChange(currentPage - 1)}
                        hasNext={currentPage * productsPerPage < totalProducts}
                        onNext={() => handlePageChange(currentPage + 1)}
                    />
                </Card>
            </div>
        </Page>
    );
}
