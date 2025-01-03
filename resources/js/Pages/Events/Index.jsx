import React, { useState } from 'react';
import { Page, Card } from '@shopify/polaris';
import EventCreateModal from '../../components/EventCreateModal';
import { router, usePage } from '@inertiajs/react'; // Import `router` and `usePage`

export default function Index() {
      const { props } = usePage();

  const [activeModal, setActiveModal] = useState(false);
console.log('csrf_token', props)
  const handleModalChange = () => setActiveModal(!activeModal);

  return (
    <Page
      title="Events Listing"
      primaryAction={{
        content: 'Create Event',
        onAction: () => setActiveModal(true),
      }}
    >
      <Card title="Events" sectioned>
        <p>List of events will appear here</p>
      </Card>

      {/* Use the EventModal component */}
      <EventCreateModal
        active={activeModal}
        onClose={handleModalChange}
      />
    </Page>
  );
}
