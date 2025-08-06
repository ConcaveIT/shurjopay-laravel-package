# shurjoPay Laravel Package

**Laravel 12 Compatible**

## Installation and Configuration

1. **Install via Composer**

    ```bash
    composer require smukhidev/shurjopay-laravel-package
    ```

2. **Register the Service Provider** (if not auto-discovered)

    In `config/app.php` providers array:

    ```php
    smukhidev\ShurjopayLaravelPackage\ShurjopayServiceProvider::class,
    ```

3. **Publish the Configuration File**

    ```bash
    php artisan vendor:publish --provider="smukhidev\\ShurjopayLaravelPackage\\ShurjopayServiceProvider" --tag=config
    ```

    This will create `config/shurjopay.php` in your project.

4. **Set Environment Variables**

    Add the following to your `.env` file:

    ```env
    SHURJOPAY_SERVER_URL=
    MERCHANT_USERNAME=
    MERCHANT_PASSWORD=
    MERCHANT_KEY_PREFIX=
    ```

## Usage

1. **Import the Service**

    ```php
    use smukhidev\ShurjopayLaravelPackage\ShurjopayService;
    ```

2. **Initiate Payment**

    ```php
    $shurjopay_service = new ShurjopayService();
    $tx_id = $shurjopay_service->generateTxId();
    $success_route = route('your.custom.route');
    $data = [
        'amount' => $request->amount,
        'custom1' => $request->company_name,
        'custom2' => $request->email,
        'custom3' => $request->name,
        'custom4' => $request->number,
        'is_emi' => 0 // 0 = No EMI, 1 = EMI active
    ];
    $shurjopay_service->sendPayment($data, $success_route);
    ```

3. **Handle the Response**

    The package registers a route `/response` (named `shurjopay.response`) that will handle the payment gateway response. You can use this in your application as needed.

4. **Decrypt Response Data**

    On your success URL, you can decrypt the response:

    ```php
    $shurjopay_service = new ShurjopayService();
    $result = $shurjopay_service->decrypt($request->spdata); // returns object
    ```

    Example response:

    ```json
    {
      "txID": "NOK20210615081852_100",
      "bankTxID": "Xid70lopzz",
      "bankTxStatus": "SUCCESS",
      "txnAmount": "100",
      "spCode": "000",
      "spCodeDes": "Cancel",
      "custom1": "Shurjomukhi Ltd",
      "custom2": "nazmus.shahadat@shurjomukhi.com.bd",
      "custom3": "Nazmus Shahadat",
      "custom4": "01829616787",
      "paymentOption": "CARD",
      "paymentTime": "2021-06-16 02:18:51"
    }
    ```

## Demo Card Information

- Card Number: 1111 1111 1111 1111
- Expiry Date: 12/30
- CVC: 123
- Card Owner: TEST

---

**This package is now compatible with Laravel 8, 9, 10, 11, and 12.**
