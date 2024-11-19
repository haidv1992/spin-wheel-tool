### Spin Wheel Tool for Laravel Nova

**Spin Wheel Tool** is a Laravel Nova package that allows you to configure a spinning wheel with a flexible frontend interface. The package supports setting prize probabilities from the backend and ensures security by handling the spinning logic on the server. It integrates seamlessly with Laravel Nova, providing an intuitive interface for administrators to manage prizes and configurations.

---

## **Features**

- **Backend Configuration**: Easily configure prize probabilities and spin wheel settings via Laravel Nova.
- **Frontend Interface**: Integrate a visually appealing spinning wheel into your frontend using Blade components.
- **Server-Side Logic**: Ensure secure and reliable spin processing on the server.
- **Customizable**: Override specific controller methods to implement custom spin logic or integrate with existing models.
- **Asset Publishing**: Publish and customize frontend assets as needed.

---

## **Requirements**

- **Laravel**: Version 8.x or higher
- **Laravel Nova**: Version 4.x or higher
- **Node.js**: Version 14.x or higher
- **PHP**: Version 7.4 or higher

---

## **Installation**

Follow these steps to install and set up the Spin Wheel Tool package in your Laravel Nova project.

### **1. Install the Package via Composer**

Run the following command to install the package:

```bash
composer require dbiz/spin-wheel-tool
```

### **2. Publish Configuration and Assets**

Publish the package's configuration file, migrations, and frontend assets using the provided Artisan command:

```bash
php artisan spinwheeltool:publish
```

*Alternatively, you can publish each asset type individually:*

```bash
php artisan vendor:publish --provider="Dbiz\SpinWheelTool\ToolServiceProvider" --tag=config
php artisan vendor:publish --provider="Dbiz\SpinWheelTool\ToolServiceProvider" --tag=migrations
php artisan vendor:publish --provider="Dbiz\SpinWheelTool\ToolServiceProvider" --tag=assets
```

### **3. Run Migrations**

The package requires a `spin_wheel_settings` table to store configuration data. Run the migrations with:

```bash
php artisan migrate
```

### **4. Install and Compile Frontend Assets**

Navigate to the `nova-components/SpinWheelTool` directory and install frontend dependencies:

```bash
cd nova-components/SpinWheelTool
yarn install
```

#### **Development Build**

To compile assets during development and watch for changes:

```bash
yarn frontend:watch
```

or

```bash
yarn watch
yarn dev
```

#### **Production Build**

To compile assets for production:

```bash
yarn frontend:build
```

or

```bash
yarn build
```

### **5. Publish Assets (Optional)**

If you need to customize the frontend interface or logic, publish the assets:

```bash
php artisan vendor:publish --tag=spin-wheel-tool-assets
```

This will copy the frontend assets to the `public/spin-wheel-tool` directory, allowing you to modify them as needed.

---

## **Usage**

### **1. Configure Prize Probabilities in the Backend**

In Laravel Nova, navigate to the **Spin Wheel Tool** menu. Here, you can configure prize probabilities and spin wheel settings.

#### **Configure Prizes**

Define your prizes with the following attributes:

- **`id`**: Unique identifier for the prize.
- **`name`**: Name of the prize.
- **`weight`**: Probability weight of the prize. The chance of winning is calculated based on the total weight.
- **`display_option`**: Determines how the prize is displayed (`icon`, `text`, or `both`).
- **`backgroundColor`**: Background color for the prize segment.

Example JSON configuration:

```json
[
  {
    "id": 1,
    "name": "Prize 1",
    "weight": 10,
    "display_option": "both",
    "backgroundColor": "#FFC20E"
  },
  {
    "id": 2,
    "name": "Prize 2",
    "weight": 20,
    "display_option": "icon",
    "backgroundColor": "#4AC6FF"
  },
  {
    "id": 3,
    "name": "Prize 3",
    "weight": 30,
    "display_option": "text",
    "backgroundColor": "#DE2B2E"
  }
]
```

#### **Configure Spin Wheel Settings**

Adjust spin wheel behavior such as spin duration, number of revolutions, easing function, and rotation resistance.

Default configuration:

```json
{
  "spinDuration": 4000,
  "numberOfRevolutions": 20,
  "easingFunction": "cubicOut",
  "rotationResistance": -35
}
```

#### **Set Spin Limit**

Define the maximum number of spins allowed per user per day in the `config/spinwheeltool.php` file:

```php
'spin_limit' => 3, // Set your desired spin limit
```

### **2. Integrate the Spinning Wheel into the Frontend**

#### **Blade Component**

Embed the spinning wheel interface into any Blade page using the following Blade component:

```blade
<x-spin-wheel />
```

This component will render the spinning wheel interface, allowing users to perform spins.

#### **Direct Route Access**

Alternatively, access the spinning interface directly via the provided route:

```
/nova-vendor/spin-wheel-tool/frontend
```

When the user clicks spin, the system will call the `/nova-vendor/spin-wheel-tool/spin` API to process the spinning logic on the server and return the result.

---

## **Customization**

### **Overriding Controller Methods**

The package allows you to override specific controller methods (`checkSpin` and `submitCustomerInfo`) to implement custom spin logic or integrate with existing models.

#### **1. Create a Custom Controller**

Create a new controller in your application that extends the package's `SpinWheelController`:

```bash
php artisan make:controller CustomSpinWheelController
```

#### **2. Implement the Custom Controller**

Implement the custom controller by extending `SpinWheelController` and overriding the desired methods:

```php
<?php

namespace App\Http\Controllers;

use Dbiz\SpinWheelTool\Http\Controllers\SpinWheelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class CustomSpinWheelController extends SpinWheelController
{
    /**
     * Override the checkSpin method with custom logic.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSpin(Request $request)
    {
        $spinLimit = Config::get('spinwheeltool.spin_limit', 3);
        $today = now()->startOfDay();

        // Implement custom spin limit logic here
        // Example: Check against a custom spin count mechanism

        // Example: Always allow spin and log
        Log::info('Custom checkSpin called.', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'spin_limit' => $spinLimit,
        ]);

        // Implement actual spin limit logic as needed
        // Return appropriate response
        return response()->json([
            'can_spin' => true, // Change based on your logic
        ]);
    }

    /**
     * Override the submitCustomerInfo method with custom logic.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitCustomerInfo(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|regex:/^\d{10,15}$/',
            'email'      => 'required|email|max:255',
            'spin_token' => 'required|uuid',
        ]);

        // Implement custom logic here
        // Example: Save customer info to a custom model or perform additional actions

        Log::info('Custom submitCustomerInfo called.', [
            'name'       => $validated['name'],
            'phone'      => $validated['phone'],
            'email'      => $validated['email'],
            'spin_token' => $validated['spin_token'],
        ]);

        // Implement actual logic as needed

        // Return custom response
        return response()->json([
            'message' => 'Customer information received and processed.',
        ]);
    }

    /**
     * Optionally, override other methods if needed.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function spin(Request $request)
    {
        // You can override the spin method as well if needed
        return parent::spin($request);
    }

    // Add more overridden methods as needed
}
```

#### **3. Update Configuration to Use the Custom Controller**

Edit the `config/spinwheeltool.php` file to specify your custom controller:

```php
<?php

return [

    // ... existing configuration ...

    /*
    |--------------------------------------------------------------------------
    | Custom Spin Wheel Controller
    |--------------------------------------------------------------------------
    |
    | Specify a custom controller to override the default SpinWheelController.
    | If not set, the package's default controller will be used.
    |
    | Example:
    | 'controller' => App\Http\Controllers\CustomSpinWheelController::class,
    |
    */
    'controller' => App\Http\Controllers\CustomSpinWheelController::class,

];
```

With this configuration, the package will use your custom controller instead of the default one, allowing you to implement tailored spin logic or integrate with your application's models.

---

## **Development**

### **1. Modify the Interface or Spinning Logic**

- **Frontend Interface**: All Vue.js components are located in the `nova-components/SpinWheelTool/resources/js/components` directory. You can edit files like `SpinWheel.vue` or other related components to customize the spinning wheel's appearance and behavior.

- **CSS**: Modify styles in `nova-components/SpinWheelTool/resources/css/tool.css` to adjust the spinning wheel's design.

#### **Recompiling Assets**

After making changes to frontend assets, recompile them:

```bash
yarn frontend:watch
```

or for production:

```bash
yarn frontend:build
```

### **2. Backend Development**

If you need to adjust backend logic or extend functionalities:

- **Controllers**: Override controller methods as described in the [Customization](#customization) section.

- **Services**: Modify or extend services like `PrizeService` located in `nova-components/SpinWheelTool/src/Services`.

#### **Recompiling Backend Assets**

After making changes to backend code, ensure that your application recognizes the updates. If necessary, clear caches:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## **Feedback and Issues**

If you encounter any issues or have suggestions, please create an issue at the [GitHub repository](https://github.com/yourusername/spin-wheel-tool).

Feel free to submit pull requests for improvements or new features!

---

## **License**

Spin Wheel Tool is released under the [MIT License](https://opensource.org/licenses/MIT).

---

## **README.md Overview**

- **Features**: Highlights the key functionalities of the Spin Wheel Tool.
- **Requirements**: Lists the necessary software and versions.
- **Installation**: Step-by-step guide to install and set up the package.
- **Usage**: Instructions on configuring prizes and integrating the spinning wheel into the frontend.
- **Customization**: Guides on overriding controller methods for custom logic.
- **Development**: Information on modifying frontend and backend components.
- **Feedback and Issues**: Directs users to the GitHub repository for support and contributions.
- **License**: States the licensing terms.

*Replace `yourusername/spin-wheel-tool` with the actual URL to your GitHub repository.*

---

### **Additional Development Commands**

#### **Frontend Development**

- **Watch for Changes**

  ```bash
  yarn frontend:watch
  ```

- **Build for Production**

  ```bash
  yarn frontend:build
  ```

#### **Publishing Assets**

```bash
php artisan vendor:publish --tag=spin-wheel-tool-assets
```

---

### **Backend Development**

- **Watch Backend Assets**

  ```bash
  yarn watch
  ```

- **Development Build**

  ```bash
  yarn dev
  ```

- **Accessing Spin Wheel Settings**

  Navigate to the following route in your application:

  ```
  /nova-vendor/spin-wheel-tool/setting
  ```

---

## **Final Notes**

By following this comprehensive guide, you can effectively install, configure, and customize the Spin Wheel Tool package within your Laravel Nova project. The package is designed to be flexible and user-friendly, allowing for extensive customization to fit your application's unique requirements.

For any further assistance or inquiries, please refer to the [GitHub repository](https://github.com/yourusername/spin-wheel-tool) or contact the maintainer directly.