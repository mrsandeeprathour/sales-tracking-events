import React, { useState, useEffect } from 'react';
import { Modal, TextField, Toast } from '@shopify/polaris';
import { router, usePage } from '@inertiajs/react';

export default function EventCreateModal({ active, onClose }) {
  const { props } = usePage();

  const [formData, setFormData] = useState({
    event_name: '',
    start_date: '',
      end_date: '',
      shop: props?.config?.shop,
     status:"active"
  });

  const [isLoading, setIsLoading] = useState(false);
  const [toastMessage, setToastMessage] = useState(null);
  const [toastError, setToastError] = useState(false); // Track toast error state
  const [errors, setErrors] = useState({});

  // Handle server-side errors and display toast
  useEffect(() => {
    if (props.errors) {
      setErrors(props.errors);
      const errorMessages = Object.values(props.errors).flat().join(', ');
      setToastMessage(errorMessages);
      setToastError(true); // Set toast type to error
    }
  }, [props.errors]);

  // Clear errors and reset toast on modal close
  useEffect(() => {
    if (!active) {
      setFormData({
        event_name: '',
        start_date: '',
          end_date: '',
           shop: props?.config?.shop,
         status:"active"
      });
      setErrors({});
      setToastMessage(null);
      setToastError(false);
    }
  }, [active]);

  // Handle input changes and clear specific field errors
  const handleInputChange = (field, value) => {
    setFormData({ ...formData, [field]: value });
    setErrors({ ...errors, [field]: null });
  };

  // Validate form fields
  const validateForm = () => {
    const newErrors = {};
    if (!formData.event_name) newErrors.event_name = 'Event name is required.';
    if (!formData.start_date) newErrors.start_date = 'Start date is required.';
    if (!formData.end_date) newErrors.end_date = 'End date is required.';
    if (
      formData.start_date &&
      formData.end_date &&
      new Date(formData.end_date) < new Date(formData.start_date)
    ) {
      newErrors.end_date = 'End date must be greater than or equal to start date.';
    }
    return newErrors;
  };

  // Handle form submission
  const handleEventCreate = async () => {
    const validationErrors = validateForm();
    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      return;
    }

    setIsLoading(true);
    try {
      await router.post(
        '/events',
        formData,
        {
          onSuccess: () => {
            setToastMessage('Event created successfully!');
            setToastError(false); // Success toast
            onClose();
          },
          onError: (serverErrors) => {
            setErrors(serverErrors || {});
            const errorMessages = Object.values(serverErrors).flat().join(', ');
            setToastMessage(errorMessages);
            setToastError(true); // Error toast
          },
        }
      );
    } catch (error) {
      console.error('Error:', error);
      setToastMessage('An unexpected error occurred.');
      setToastError(true);
    }
  };

  return (
    <>
      <Modal
        open={active}
        onClose={onClose}
        title="Create New Event"
        primaryAction={{
          content: isLoading ? 'Creating...' : 'Create',
          onAction: handleEventCreate,
          disabled: isLoading,
        }}
        secondaryActions={[
          {
            content: 'Cancel',
            onAction: onClose,
          },
        ]}
      >
        <Modal.Section>
          <TextField
            label="Event Name"
            value={formData.event_name}
            onChange={(value) => handleInputChange('event_name', value)}
            error={errors.event_name}
            autoComplete="off"
          />
          <TextField
            label="Start Date"
            type="date"
            value={formData.start_date}
            onChange={(value) => handleInputChange('start_date', value)}
            error={errors.start_date}
            autoComplete="off"
          />
          <TextField
            label="End Date"
            type="date"
            value={formData.end_date}
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
          error={toastError} // Show error style if true
        />
      )}
    </>
  );
}
