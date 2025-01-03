import React from 'react';
import { Modal, TextField, Toast } from '@shopify/polaris';
import { useForm } from '@inertiajs/react';


import axios from 'axios';



export default function EventCreateModal({ active, onClose }) {




  // Initialize useForm hook
  const { data, setData, post, processing, errors, reset } = useForm({
    event_name: '',
    start_date: '',
    end_date: '',
    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
  });

  // Handle form input changes
  const handleInputChange = (field, value) => {
      setData(field, value); // Update form data
      axios.get('/sanctum/csrf-cookie').then(() => {
        console.log('CSRF token refreshed.');
    });
  };

  // Handle form submission
  const handleEventCreate = () => {
    post('/events', {
      onSuccess: () => {
        // Show success message and reset the form
        setToastMessage('Event created successfully!');
        reset();
        onClose(); // Close the modal
      },
      onError: () => {
        // Errors will be handled automatically by `errors` object
        setToastMessage('Failed to create event.');
      },
    });
  };

  // Toast message state
  const [toastMessage, setToastMessage] = React.useState(null);

  return (
    <>
      <Modal
        open={active}
        onClose={() => {
          reset(); // Reset the form when closing the modal
          onClose();
        }}
        title="Create New Event"
        primaryAction={{
          content: processing ? 'Creating...' : 'Create',
          onAction: handleEventCreate,
          disabled: processing, // Disable button while processing
        }}
        secondaryActions={[
          {
            content: 'Cancel',
            onAction: () => {
              reset(); // Reset the form on cancel
              onClose();
            },
          },
        ]}
      >
        <Modal.Section>
          <TextField
            label="Event Name"
            value={data.event_name}
            onChange={(value) => handleInputChange('event_name', value)}
            error={errors.event_name}
            autoComplete="off"
          />
          <TextField
            label="Start Date"
            type="date"
            value={data.start_date}
            onChange={(value) => handleInputChange('start_date', value)}
            error={errors.start_date}
            autoComplete="off"
          />
          <TextField
            label="End Date"
            type="date"
            value={data.end_date}
            onChange={(value) => handleInputChange('end_date', value)}
            error={errors.end_date}
            autoComplete="off"
          />
        </Modal.Section>
      </Modal>

      {toastMessage && (
        <Toast
          content={toastMessage}
          onDismiss={() => setToastMessage(null)}
        />
      )}
    </>
  );
}
