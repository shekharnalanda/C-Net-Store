import 'package:flutter/material.dart';

import '../core/api_client.dart';

class OrdersScreen extends StatelessWidget {
  const OrdersScreen({super.key});
  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Orders')),
    body: FutureBuilder<Map<String, dynamic>>(
      future: ApiClient().orders(),
      builder: (_, snapshot) {
        if (snapshot.connectionState != ConnectionState.done)
          return const Center(child: CircularProgressIndicator());
        final orders =
            ((snapshot.data?['data'] as Map<String, dynamic>?)?['data']
                as List<dynamic>? ??
            []);
        if (orders.isEmpty)
          return const Center(
            child: Text('New customer orders will appear here.'),
          );
        return ListView.separated(
          padding: const EdgeInsets.all(16),
          itemCount: orders.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (_, i) {
            final order = orders[i] as Map<String, dynamic>;
            return Card(
              child: ListTile(
                title: Text(order['order_number']?.toString() ?? 'Order'),
                subtitle: Text(
                  '${order['status']} • ${order['fulfilment_type']}',
                ),
                trailing: Text(
                  '₹${order['grand_total'] ?? 0}',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                onTap: () {},
              ),
            );
          },
        );
      },
    ),
  );
}
