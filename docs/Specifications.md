# Specifications

## Overview

Specifications are the building blocks of the Product Specifications plugin. They represent individual attributes or characteristics of a product that you want to highlight or compare. This section will guide you through creating, managing, and using specifications in your WordPress e-commerce store.

## Creating a New Specification

To create a new specification:

1. Navigate to your WordPress admin panel.
2. Go to "Product Specs" > "Specifications" in the left sidebar.
3. Click the "Add New Specification" button at the top of the page.
4. Fill in the following fields:
   - **Name**: Enter a descriptive name for the specification (e.g., "Screen Size", "Battery Life").
   - **Slug**: This will be automatically generated based on the name, but you can modify it if needed.
   - **Field Type**: Choose the appropriate field type for the specification (see "Field Types" section below).
   - **Description**: (Optional) Add any additional information about this specification.
5. Click "Save" to create the new specification.

## Field Types

The plugin supports various field types to accommodate different kinds of product specifications:

1. **Text**: A single-line text input for short, simple values.
   Example: Model Number

2. **Textarea**: A multi-line text area for longer descriptions or lists.
   Example: Features List

3. **Select**: A dropdown menu with predefined options.
   Example: Color (with options like Red, Blue, Green)

4. **True/False**: A boolean field for yes/no or true/false attributes.
   Example: Is Water Resistant

5. **Number**: A field that accepts only numeric input.
   Example: Weight (in kg)

(Note: Additional field types may be available in future updates.)

## Managing Specifications

### Editing a Specification

1. Go to "Product Specs" > "Specifications".
2. Find the specification you want to edit in the list.
3. Click on the specification name or the "Edit" link.
4. Make your changes in the edit screen.
5. Click "Update" to save your changes.

### Deleting a Specification

1. Go to "Product Specs" > "Specifications".
2. Hover over the specification you want to delete.
3. Click the "Delete" link that appears.
4. Confirm the deletion when prompted.

Note: Deleting a specification will remove it from all products and specification bundles where it was used.

### Bulk Actions

You can perform actions on multiple specifications at once:

1. Select the checkboxes next to the specifications you want to modify.
2. Choose an action from the "Bulk Actions" dropdown (e.g., Delete).
3. Click "Apply" to execute the action.

## Using Specifications

Once you've created specifications, you can use them in several ways:

1. **Adding to Products**: When editing a product, you'll find a "Specifications" meta box where you can add and fill in relevant specifications.

2. **Creating Specification Bundles**: Group related specifications together for easy application to similar products. (See "Specification Bundles" documentation for more details.)

3. **Displaying on Product Pages**: The plugin will automatically display the specifications on your product pages. You can customize this display in the plugin settings.

4. **Product Comparison**: Customers can use these specifications to compare different products side-by-side.

## Best Practices

- Use clear, concise names for your specifications.
- Be consistent in your naming conventions and units of measurement.
- Group related specifications together in bundles for easier management.
- Regularly review and update your specifications to ensure they remain relevant and accurate.

## Troubleshooting

If you encounter any issues while creating or managing specifications:

1. Ensure your WordPress and plugin versions are up to date.
2. Check that the specification slug is unique.
3. Verify that you have the necessary permissions to create and edit specifications.
4. If problems persist, check the plugin's support forum or contact support for assistance.

For more advanced usage and customization options, please refer to our API documentation and developer guides.
