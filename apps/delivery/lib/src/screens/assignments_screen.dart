import 'package:flutter/material.dart';

import '../core/api_client.dart';

class AssignmentsScreen extends StatelessWidget {
  const AssignmentsScreen({super.key});
  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('My Deliveries')),
    body: FutureBuilder<Map<String, dynamic>>(
      future: ApiClient().assignments(),
      builder: (_, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        final page = snapshot.data?['data'] as Map<String, dynamic>?;
        final rows = page?['data'] as List<dynamic>? ?? [];
        if (rows.isEmpty) {
          return const Center(child: Text('No delivery assignments yet.'));
        }
        return ListView.builder(
          itemCount: rows.length,
          itemBuilder: (_, i) {
            final row = rows[i] as Map<String, dynamic>;
            return ListTile(
              leading: const Icon(Icons.delivery_dining),
              title: Text('Delivery #${row['id']}'),
              subtitle: Text(row['status']?.toString() ?? 'assigned'),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => AssignmentDetail(data: row)),
              ),
            );
          },
        );
      },
    ),
  );
}

class AssignmentDetail extends StatefulWidget {
  const AssignmentDetail({super.key, required this.data});
  final Map<String, dynamic> data;
  @override
  State<AssignmentDetail> createState() => _AssignmentDetailState();
}

class _AssignmentDetailState extends State<AssignmentDetail> {
  final otp = TextEditingController();
  bool busy = false;
  Future<void> update(String status) async {
    setState(() => busy = true);
    try {
      await ApiClient().updateAssignment(
        widget.data['id'] as int,
        status,
        pickupOtp: status == 'picked_up' ? otp.text : null,
        deliveryOtp: status == 'delivered' ? otp.text : null,
      );
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Delivery details')),
    body: Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          TextField(
            controller: otp,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Pickup / Delivery OTP',
            ),
          ),
          ElevatedButton(
            onPressed: busy ? null : () => update('accepted'),
            child: const Text('Accept Assignment'),
          ),
          OutlinedButton(
            onPressed: busy ? null : () => update('picked_up'),
            child: const Text('Confirm Pickup'),
          ),
          OutlinedButton(
            onPressed: busy ? null : () => update('delivered'),
            child: const Text('Confirm Delivery'),
          ),
        ],
      ),
    ),
  );
}
