Here is the cleaned-up **README.md** without unnecessary local path references:

---

# **Spin Wheel Tool for Laravel Nova**

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

Alternatively, publish individual asset types:

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

---

## **Integration**

### **1. Register the Tool in NovaServiceProvider**

To register the tool in Laravel Nova, open your `NovaServiceProvider` file and add the `SpinWheelTool` to the `tools()` method:

```php
public function tools()
{
    return [
        new \Dbiz\SpinWheelTool\SpinWheelTool(),
    ];
}
```

### **2. Add Menu Section for Backend Navigation**

Add a menu section for the Spin Wheel Tool in your Nova menu configuration:

```php
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Menu\MenuItem;

public function menu()
{
    return [
        MenuSection::make('Quay thưởng', [
            MenuItem::make('Cấu hình', '/nova-vendor/spin-wheel-tool/setting'),
        ])->icon('refresh')->collapsable(),
    ];
}
```

This will add a collapsible "Quay thưởng" section in the Nova backend, allowing administrators to access the Spin Wheel Tool's configuration page.

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

---

## **Development**

### **1. Modify the Interface or Spinning Logic**

- **Frontend Interface**: All Vue.js components are located in the `nova-components/SpinWheelTool/resources/js/components` directory. You can edit files like `SpinWheel.vue` or other related components to customize the spinning wheel's appearance and behavior.

- **CSS**: Modify styles in `nova-components/SpinWheelTool/resources/css/tool.css` to adjust the spinning wheel's design.

---

## **Final Notes**

By following this comprehensive guide, you can effectively install, configure, and customize the Spin Wheel Tool package within your Laravel Nova project. For any further assistance or inquiries, please refer to the [GitHub repository](https://github.com/yourusername/spin-wheel-tool) or contact the maintainer directly.