import React, { useState, useEffect } from 'react';
import { Page, Card, IndexTable, Badge, Text, Pagination } from '@shopify/polaris';
import { usePage, router } from '@inertiajs/react';
import EventCreateModal from '../../components/EventCreateModal';

export default function Index() {
  const { props } = usePage();
  const [activeModal, setActiveModal] = useState(false);
  const [events, setEvents] = useState([]); // The list of events
  const [currentPage, setCurrentPage] = useState(props?.events?.current_page); // Track current page
  const [totalEvents, setTotalEvents] = useState(props?.events?.total); // Total number of events
  const [lastPage, setLastPage] = useState(props?.events?.last_page); // Last page number
  const pageSize = 10; // Page size (number of rows per page)

  useEffect(() => {
    // Set events data from props
    if (props.events && props.events.data) {
      setEvents(props.events.data); // Paginated data
      setTotalEvents(props.events.total); // Total events for pagination
      setLastPage(props.events.last_page); // Last page number
    }
  }, [props.events]);

  // Handle page change
  const handlePageChange = (newPage) => {
    setCurrentPage(newPage);
    // Trigger the page change by sending the page number to the backend
    router.get(`/events?page=${newPage}`, { preserveState: true });
  };

  // Handle row click for navigation to event details page
    const handleRowClick = (eventId) => {
      console.log('eventId',eventId)
    router.get(`/events/${eventId}`);
  };

  // Calculate the rows to display on the current page
  const rows = events.map((event) => ({
    id: event.id,
    content: [
      event.event_name,
      event.start_date,
      event.end_date,
      event.status === 'active' ? (
        <Badge progress="complete">Active</Badge>
      ) : (
        <Badge progress="incomplete">Inactive</Badge>
      ),
    ],
  }));

  const resourceName = {
    singular: 'event',
    plural: 'events',
  };

  return (
    <Page
      title="Events Listing"
      primaryAction={{
        content: 'Create Event',
        onAction: () => setActiveModal(true),
      }}
    >
      <Card title="Events" sectioned>
        <IndexTable
          resourceName={resourceName}
          itemCount={totalEvents}
          page={currentPage}
          onPageChange={handlePageChange}
          hasMoreItems={currentPage < lastPage}
          headings={[
            { title: 'Event Name' },
            { title: 'Start Date' },
            { title: 'End Date' },
            { title: 'Status' },
          ]}
        >
          {rows.map(({ id, content }) => (
            <IndexTable.Row key={id} id={id} onClick={() => handleRowClick(id)}>
              {content.map((cellContent, index) => (
                <IndexTable.Cell key={index}>
                  <Text variant="bodyMd" fontWeight="bold" as="span">
                    {cellContent}
                  </Text>
                </IndexTable.Cell>
              ))}
            </IndexTable.Row>
          ))}
        </IndexTable>

        {/* Pagination Controls */}
        <Pagination
          hasPrevious={currentPage > 1}
          hasNext={currentPage < lastPage}
          onPrevious={() => handlePageChange(currentPage - 1)}
          onNext={() => handlePageChange(currentPage + 1)}
          label={`${(currentPage - 1) * pageSize + 1}-${currentPage * pageSize} of ${totalEvents} events`}
        />
      </Card>

      {/* Use the EventModal component */}
      <EventCreateModal active={activeModal} onClose={() => setActiveModal(false)} />
    </Page>
  );
}
