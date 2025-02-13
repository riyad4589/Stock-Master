<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Gloudemans\Shoppingcart\Facades\Cart;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class ProductCart extends Component
{
    use LivewireAlert;

    /** @var array<string> */
    public $listeners = ['productSelected', 'discountModalRefresh'];

    public $cart_instance;

    public $global_discount;

    public $global_tax;

    public $shipping;

    public $quantity;

    public $price;

    public $check_quantity;

    public $discount_type;

    public $item_discount;

    public $data;

    public function mount($cartInstance, $data = null)
    {
        $this->cart_instance = $cartInstance;

        if ($data) {
            $this->data = $data;

            $this->global_discount = $data->discount_percentage;
            $this->global_tax = $data->tax_percentage;
            $this->shipping = $data->shipping_amount;

            $this->updatedGlobalTax();
            $this->updatedGlobalDiscount();

            $cart_items = Cart::instance($this->cart_instance)->content();

            foreach ($cart_items as $cart_item) {
                $this->check_quantity[$cart_item->id] = [$cart_item->options->stock];
                $this->quantity[$cart_item->id] = $cart_item->qty;
                $this->discount_type[$cart_item->id] = $cart_item->options->product_discount_type;

                if ($cart_item->options->product_discount_type === 'fixed') {
                    $this->item_discount[$cart_item->id] = $cart_item->options->product_discount;
                } elseif ($cart_item->options->product_discount_type === 'percentage') {
                    $this->item_discount[$cart_item->id] = round(100 * $cart_item->options->product_discount / $cart_item->price);
                }
            }
        } else {
            $this->global_discount = 0;
            $this->global_tax = 0;
            $this->shipping = 0.00;
            $this->check_quantity = [];
            $this->quantity = [];
            $this->discount_type = [];
            $this->item_discount = [];
        }
    }

    public function render()
    {
        $cart_items = Cart::instance($this->cart_instance)->content();

        return view('livewire.product-cart', [
            'cart_items' => $cart_items,
        ]);
    }

    public function productSelected($product)
    {
        $cart = Cart::instance($this->cart_instance);

        $exists = $cart->search(function ($cartItem, $rowId) use ($product) {
            return $cartItem->id === $product['id'];
        });

        if ($exists->isNotEmpty()) {
            $this->alert('error', 'Product already exists in cart!');

            return;
        }

        $cart->add([
            'id' => $product['id'],
            'name' => $product['name'],
            'qty' => 1,
            'price' => $this->calculate($product)['price'],
            'weight' => 1,
            'options' => [
                'product_discount' => 0.00,
                'product_discount_type' => 'fixed',
                'sub_total' => $this->calculate($product)['sub_total'],
                'code' => $product['code'],
                'stock' => $product['quantity'],
                'unit' => $product['unit'],
                'product_tax' => $this->calculate($product)['product_tax'],
                'unit_price' => $this->calculate($product)['unit_price'],
            ],
        ]);

        $this->check_quantity[$product['id']] = $product['quantity'];
        $this->price[$product['id']] = $product['price'];
        $this->quantity[$product['id']] = 1;
        $this->discount_type[$product['id']] = 'fixed';
        $this->item_discount[$product['id']] = 0;
    }

    public function removeItem($row_id)
    {
        Cart::instance($this->cart_instance)->remove($row_id);
    }

    public function updatedGlobalTax()
    {
        Cart::instance($this->cart_instance)->setGlobalTax((int) $this->global_tax);
    }

    public function updatedGlobalDiscount()
    {
        Cart::instance($this->cart_instance)->setGlobalDiscount((int) $this->global_discount);
    }

    public function updateQuantity($row_id, $product_id)
    {
        if ($this->cart_instance === 'sale' || $this->cart_instance === 'purchase_return') {
            if ($this->check_quantity[$product_id] < $this->quantity[$product_id]) {
                $this->alert('error', 'Quantity is greater than in stock!');

                return;
            }
        }

        Cart::instance($this->cart_instance)->update($row_id, $this->quantity[$product_id]);

        $cart_item = Cart::instance($this->cart_instance)->get($row_id);

        Cart::instance($this->cart_instance)->update($row_id, [
            'options' => [
                'sub_total' => $cart_item->price * $cart_item->qty,
                'code' => $cart_item->options->code,
                'stock' => $cart_item->options->stock,
                'unit' => $cart_item->options->unit,
                'product_tax' => $cart_item->options->product_tax,
                'unit_price' => $cart_item->options->unit_price,
                'product_discount' => $cart_item->options->product_discount,
                'product_discount_type' => $cart_item->options->product_discount_type,
            ],
        ]);
    }

    public function updatedDiscountType($value, $name)
    {
        $this->item_discount[$name] = 0;
    }

    public function discountModalRefresh($product_id, $row_id)
    {
        $this->updateQuantity($row_id, $product_id);
    }

    public function productDiscount($row_id, $product_id)
    {
        $cart_item = Cart::instance($this->cart_instance)->get($row_id);

        if ($this->discount_type[$product_id] === 'fixed') {
            Cart::instance($this->cart_instance)
                ->update($row_id, [
                    'price' => $cart_item->price + $cart_item->options->product_discount - $this->item_discount[$product_id],
                ]);

            $discount_amount = $this->item_discount[$product_id];

            $this->updateCartOptions($row_id, $product_id, $cart_item, $discount_amount);
        } elseif ($this->discount_type[$product_id] === 'percentage') {
            $discount_amount = ($cart_item->price + $cart_item->options->product_discount) * $this->item_discount[$product_id] / 100;

            Cart::instance($this->cart_instance)
                ->update($row_id, [
                    'price' => $cart_item->price + $cart_item->options->product_discount - $discount_amount,
                ]);

            $this->updateCartOptions($row_id, $product_id, $cart_item, $discount_amount);
        }
        $this->alert('success', __('Discount applied successfully!'));
    }

    public function calculate($product)
    {
        $price = 0;
        $unit_price = 0;
        $product_tax = 0;
        $sub_total = 0;

        if ($product['tax_type'] === 1) {
            $price = $product['price'] + ($product['price'] * $product['order_tax'] / 100);
            $unit_price = $product['price'];
            $product_tax = $product['price'] * $product['order_tax'] / 100;
            $sub_total = $product['price'] + ($product['price'] * $product['order_tax'] / 100);
        } elseif ($product['tax_type'] === 2) {
            $price = $product['price'];
            $unit_price = $product['price'] - ($product['price'] * $product['order_tax'] / 100);
            $product_tax = $product['price'] * $product['order_tax'] / 100;
            $sub_total = $product['price'];
        } else {
            $price = $product['price'];
            $unit_price = $product['price'];
            $product_tax = 0.00;
            $sub_total = $product['price'];
        }

        return ['price' => $price, 'unit_price' => $unit_price, 'product_tax' => $product_tax, 'sub_total' => $sub_total];
    }

    public function updateCartOptions($row_id, $product_id, $cart_item, $discount_amount)
    {
        Cart::instance($this->cart_instance)->update($row_id, ['options' => [
            'sub_total' => $cart_item->price * $cart_item->qty,
            'code' => $cart_item->options->code,
            'stock' => $cart_item->options->stock,
            'unit' => $cart_item->options->unit,
            'product_tax' => $cart_item->options->product_tax,
            'unit_price' => $cart_item->options->unit_price,
            'product_discount' => $discount_amount,
            'product_discount_type' => $this->discount_type[$product_id],
        ],
        ]);
    }
}
