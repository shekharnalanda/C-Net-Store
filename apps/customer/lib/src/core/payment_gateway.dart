import 'package:razorpay_flutter/razorpay_flutter.dart';

import 'app_config.dart';

class PaymentGateway {
  PaymentGateway({
    required void Function(PaymentSuccessResponse) onSuccess,
    required void Function(PaymentFailureResponse) onFailure,
    required void Function(ExternalWalletResponse) onExternalWallet,
  }) {
    _razorpay
      ..on(Razorpay.EVENT_PAYMENT_SUCCESS, onSuccess)
      ..on(Razorpay.EVENT_PAYMENT_ERROR, onFailure)
      ..on(Razorpay.EVENT_EXTERNAL_WALLET, onExternalWallet);
  }

  final Razorpay _razorpay = Razorpay();

  void open({
    required String providerOrderId,
    required int amountInPaise,
    required String customerName,
    required String customerPhone,
    String? customerEmail,
    String description = 'C-Net Store order payment',
  }) {
    if (AppConfig.razorpayKeyId.isEmpty) {
      throw StateError('RAZORPAY_KEY_ID must be supplied at build time.');
    }
    _razorpay.open({
      'key': AppConfig.razorpayKeyId,
      'order_id': providerOrderId,
      'amount': amountInPaise,
      'name': 'C-Net Store',
      'description': description,
      'prefill': {
        'name': customerName,
        'contact': customerPhone,
        'email': customerEmail,
      },
      'theme': {'color': '#1268D8'},
      'retry': {'enabled': true, 'max_count': 2},
    });
  }

  void dispose() => _razorpay.clear();
}
