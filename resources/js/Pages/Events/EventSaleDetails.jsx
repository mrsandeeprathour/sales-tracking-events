import React from 'react';
import { usePage } from '@inertiajs/react';
import {
  Page,
  Layout,
  Card,
  Grid,
  Thumbnail,
  Text
} from '@shopify/polaris';

const EventSaleDetails = () => {
  const { props } = usePage();
  console.log('props', props);

  // Ensure data is available and loop through eventSale object
  const dataToDisplay = props?.eventSale
    ? Object.values(props?.eventSale) // Convert object to array of sales
    : [];

  return (
    <Page title={`Event Sale Details`} backAction={{ content: 'Back', onAction: () => window.history.back() }}>
      <Layout>
        <Layout.Section>
          <Card sectioned>
            {/* Render product variants */}
            <div className="variant-list listing-outer">
              {dataToDisplay.flatMap((sale, saleIndex) =>
                sale.variants.map((variant, variantIndex) => (
                  <Grid key={`${saleIndex}-${variantIndex}`} style={{ marginBottom: '20px' }}>
                    <Grid.Cell columnSpan={{ xs: 6, sm: 3, md: 3, lg: 2, xl: 1 }}>
                      {/* Displaying images for each variant */}
                      {variant.images && variant.images.length > 0 && (
                        <Thumbnail
                          source={variant.images[0].src}
                          alt={variant.title}
                          size="large"
                        />
                      )}
                    </Grid.Cell>
                    <Grid.Cell columnSpan={{ xs: 6, sm: 3, md: 7, lg: 7, xl: 7 }}>
                      <Text variant="headingSm">{sale.title} - {variant.title}</Text>
                      <Text variant="bodySm"><b>SKU:</b> {variant.sku}</Text>
                      <Text variant="bodySm"><b>Total Inventory:</b> {variant.total_inventory}</Text>
                      <Text variant="bodySm"><b>Sold Inventory:</b> {variant.sold_inventory}</Text>
                      <Text variant="bodySm"><b>In-Hand Inventory:</b> {variant.inhand_inventory}</Text>
                      <Text variant="bodySm"><b>Price:</b> ${variant.price}</Text>
                    </Grid.Cell>
                  </Grid>
                ))
              )}
            </div>
          </Card>
        </Layout.Section>
      </Layout>
    </Page>
  );
};

export default EventSaleDetails;
