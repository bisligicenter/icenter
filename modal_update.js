// JavaScript code to update the form submission handling for existing reservation modal
// Replace the existing error handling in both form submission sections

// In the first form submission section (with payment), replace:
} else {
    throw new Error(data.message || 'Failed to make reservation');
}

// With:
} else {
    // Check if we need to show a specific modal
    if (data.show_modal === 'existing_reservation') {
        showExistingReservationModal();
    } else {
        throw new Error(data.message || 'Failed to make reservation');
    }
}

// In the second form submission section (without payment), replace:
} else {
    throw new Error(data.message || 'Failed to make reservation');
}

// With:
} else {
    // Check if we need to show a specific modal
    if (data.show_modal === 'existing_reservation') {
        showExistingReservationModal();
    } else {
        throw new Error(data.message || 'Failed to make reservation');
    }
}

// Add these modal functions:
function showExistingReservationModal() {
    showModal('existingReservationModal');
}

function hideExistingReservationModal() {
    hideModal('existingReservationModal');
} 