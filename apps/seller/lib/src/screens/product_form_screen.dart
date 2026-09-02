import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../core/api_client.dart';
import 'image_library_screen.dart';

class ProductFormScreen extends StatefulWidget {
  const ProductFormScreen({super.key, this.product});

  final Map<String, dynamic>? product;

  @override
  State<ProductFormScreen> createState() => _ProductFormScreenState();
}

class _ProductFormScreenState extends State<ProductFormScreen> {
  final formKey = GlobalKey<FormState>();
  final name = TextEditingController();
  final sku = TextEditingController();
  final description = TextEditingController();
  final price = TextEditingController();
  final salePrice = TextEditingController();
  final stock = TextEditingController(text: '0');
  final unit = TextEditingController();
  final preparation = TextEditingController();

  List<dynamic> businesses = <dynamic>[];
  List<dynamic> categories = <dynamic>[];
  int? businessId;
  int? categoryId;
  String productType = 'shopping';
  Map<String, dynamic>? libraryImage;
  XFile? customImage;
  bool vegetarian = false;
  bool loading = true;
  bool saving = false;
  String? error;

  bool get editing => widget.product != null;

  @override
  void initState() {
    super.initState();
    final item = widget.product;
    if (item != null) {
      name.text = item['name']?.toString() ?? '';
      sku.text = item['sku']?.toString() ?? '';
      description.text = item['description']?.toString() ?? '';
      price.text = item['price']?.toString() ?? '';
      salePrice.text = item['sale_price']?.toString() ?? '';
      stock.text = item['stock_quantity']?.toString() ?? '0';
      unit.text = item['unit']?.toString() ?? '';
      preparation.text = item['preparation_minutes']?.toString() ?? '';
      businessId = _int(item['business_id']);
      categoryId = _int(item['category_id']);
      productType = item['product_type']?.toString() ?? 'shopping';
      vegetarian = item['is_vegetarian'] == true;
      if (item['library_image'] is Map<String, dynamic>) {
        libraryImage = item['library_image'] as Map<String, dynamic>;
      }
    }
    _load();
  }

  int? _int(dynamic value) => int.tryParse(value?.toString() ?? '');

  Future<void> _load() async {
    try {
      final response = await ApiClient().businesses();
      final rows = response['data'] as List<dynamic>? ?? <dynamic>[];
      final approved = rows.where((item) {
        final row = item as Map<String, dynamic>;
        return row['status']?.toString().toLowerCase() == 'approved';
      }).toList();
      final categoryRows =
          await ApiClient().productCategories(productType: productType);
      if (!mounted) {
        return;
      }
      setState(() {
        businesses = approved;
        categories = categoryRows;
        businessId ??= approved.isEmpty
            ? null
            : _int((approved.first as Map<String, dynamic>)['id']);
        loading = false;
      });
    } catch (exception) {
      if (mounted) {
        setState(() { error = _message(exception); loading = false; });
      }
    }
  }

  Future<void> _changeType(String? value) async {
    if (value == null || value == productType) {
      return;
    }
    setState(() {
      productType = value;
      categoryId = null;
      libraryImage = null;
      customImage = null;
      categories = <dynamic>[];
    });
    try {
      final rows = await ApiClient().productCategories(productType: value);
      if (mounted) {
        setState(() => categories = rows);
      }
    } catch (exception) {
      if (mounted) {
        _show(_message(exception));
      }
    }
  }

  Future<void> _chooseLibrary() async {
    if (categoryId == null) {
      _show('Please select a category first.');
      return;
    }
    final selected = await Navigator.push<Map<String, dynamic>>(
      context,
      MaterialPageRoute(
        builder: (_) => ImageLibraryScreen(categoryId: categoryId),
      ),
    );
    if (selected != null && mounted) {
      setState(() { libraryImage = selected; customImage = null; });
    }
  }

  Future<void> _chooseCustom() async {
    final selected = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 88,
      maxWidth: 1800,
    );
    if (selected != null && mounted) {
      setState(() { customImage = selected; libraryImage = null; });
    }
  }

  Future<void> _save() async {
    if (!(formKey.currentState?.validate() ?? false)) {
      return;
    }
    if (businessId == null || categoryId == null) {
      _show('Approved business and category are required.');
      return;
    }
    final existingImage = editing && widget.product?['image_url'] != null;
    if (libraryImage == null && customImage == null && !existingImage) {
      _show('Select a library image or upload your own photo.');
      return;
    }

    final payload = <String, dynamic>{
      'business_id': businessId,
      'category_id': categoryId,
      'product_type': productType,
      'name': name.text.trim(),
      if (sku.text.trim().isNotEmpty) 'sku': sku.text.trim(),
      if (description.text.trim().isNotEmpty)
        'description': description.text.trim(),
      'price': price.text.trim(),
      if (salePrice.text.trim().isNotEmpty)
        'sale_price': salePrice.text.trim(),
      'tax_rate': 0,
      'stock_quantity': stock.text.trim().isEmpty ? 0 : stock.text.trim(),
      if (unit.text.trim().isNotEmpty) 'unit': unit.text.trim(),
      if (productType == 'food' && preparation.text.trim().isNotEmpty)
        'preparation_minutes': preparation.text.trim(),
      if (productType == 'food') 'is_vegetarian': vegetarian,
      if (libraryImage != null)
        'product_image_asset_id': _int(libraryImage!['id']),
    };

    setState(() => saving = true);
    try {
      final response = editing
          ? await ApiClient().updateProduct(
              _int(widget.product!['id'])!,
              payload,
              imagePath: customImage?.path,
            )
          : await ApiClient().createProduct(
              payload,
              imagePath: customImage?.path,
            );
      if (!mounted) {
        return;
      }
      _show(response['message']?.toString() ?? 'Product saved.');
      Navigator.pop(context, true);
    } catch (exception) {
      if (mounted) {
        _show(_message(exception));
      }
    } finally {
      if (mounted) {
        setState(() => saving = false);
      }
    }
  }

  String _message(Object exception) {
    if (exception is DioException && exception.response?.data is Map) {
      final data = exception.response!.data as Map;
      final errors = data['errors'];
      if (errors is Map && errors.isNotEmpty) {
        final first = errors.values.first;
        if (first is List && first.isNotEmpty) {
          return first.first.toString();
        }
      }
      return data['message']?.toString() ?? 'Request failed.';
    }
    return exception.toString();
  }

  void _show(String text) => ScaffoldMessenger.of(context)
      .showSnackBar(SnackBar(content: Text(text)));

  String? _required(String? value) =>
      value == null || value.trim().isEmpty ? 'Required' : null;

  String? _price(String? value) {
    final amount = double.tryParse(value ?? '');
    return amount == null || amount <= 0 ? 'Enter a valid price' : null;
  }

  @override
  void dispose() {
    for (final controller in [
      name, sku, description, price, salePrice, stock, unit, preparation,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(editing ? 'Edit Product' : 'Add Product')),
        body: loading
            ? const Center(child: CircularProgressIndicator())
            : error != null
                ? Center(child: Text(error!))
                : businesses.isEmpty
                    ? const Center(
                        child: Padding(
                          padding: EdgeInsets.all(24),
                          child: Text(
                            'Your business must be approved before adding products.',
                            textAlign: TextAlign.center,
                          ),
                        ),
                      )
                    : Form(
                        key: formKey,
                        child: ListView(
                          padding: const EdgeInsets.all(16),
                          children: [
                            DropdownButtonFormField<int>(
                              initialValue: businessId,
                              decoration: const InputDecoration(labelText: 'Business'),
                              items: businesses.map((item) {
                                final row = item as Map<String, dynamic>;
                                return DropdownMenuItem<int>(
                                  value: _int(row['id']),
                                  child: Text(row['name'].toString()),
                                );
                              }).toList(),
                              onChanged: editing ? null : (v) => setState(() => businessId = v),
                            ),
                            const SizedBox(height: 12),
                            DropdownButtonFormField<String>(
                              initialValue: productType,
                              decoration: const InputDecoration(labelText: 'Marketplace'),
                              items: const [
                                DropdownMenuItem(value: 'shopping', child: Text('Shopping')),
                                DropdownMenuItem(value: 'grocery', child: Text('Grocery')),
                                DropdownMenuItem(value: 'food', child: Text('Food')),
                              ],
                              onChanged: _changeType,
                            ),
                            const SizedBox(height: 12),
                            DropdownButtonFormField<int>(
                              initialValue: categoryId,
                              decoration: const InputDecoration(labelText: 'Category'),
                              items: categories.map((item) {
                                final row = item as Map<String, dynamic>;
                                return DropdownMenuItem<int>(
                                  value: _int(row['id']),
                                  child: Text(row['name'].toString()),
                                );
                              }).toList(),
                              onChanged: (v) => setState(() {
                                categoryId = v;
                                libraryImage = null;
                                customImage = null;
                              }),
                              validator: (v) => v == null ? 'Required' : null,
                            ),
                            const SizedBox(height: 12),
                            TextFormField(controller: name, decoration: const InputDecoration(labelText: 'Product name'), validator: _required),
                            const SizedBox(height: 12),
                            TextFormField(controller: sku, decoration: const InputDecoration(labelText: 'SKU (optional)')),
                            const SizedBox(height: 12),
                            TextFormField(controller: description, maxLines: 3, decoration: const InputDecoration(labelText: 'Description')),
                            const SizedBox(height: 12),
                            Row(children: [
                              Expanded(child: TextFormField(controller: price, keyboardType: const TextInputType.numberWithOptions(decimal: true), decoration: const InputDecoration(labelText: 'Price'), validator: _price)),
                              const SizedBox(width: 12),
                              Expanded(child: TextFormField(controller: salePrice, keyboardType: const TextInputType.numberWithOptions(decimal: true), decoration: const InputDecoration(labelText: 'Sale price'))),
                            ]),
                            const SizedBox(height: 12),
                            Row(children: [
                              Expanded(child: TextFormField(controller: stock, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Stock'))),
                              const SizedBox(width: 12),
                              Expanded(child: TextFormField(controller: unit, decoration: const InputDecoration(labelText: 'Unit'))),
                            ]),
                            if (productType == 'food') ...[
                              const SizedBox(height: 12),
                              TextFormField(controller: preparation, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Preparation minutes')),
                              SwitchListTile(contentPadding: EdgeInsets.zero, title: const Text('Vegetarian product'), value: vegetarian, onChanged: (v) => setState(() => vegetarian = v)),
                            ],
                            const SizedBox(height: 18),
                            Text('Product image', style: Theme.of(context).textTheme.titleMedium),
                            if (libraryImage != null)
                              ListTile(contentPadding: EdgeInsets.zero, leading: Image.network(libraryImage!['image_url'].toString(), width: 56, height: 56, fit: BoxFit.cover), title: Text(libraryImage!['name']?.toString() ?? 'Library image'))
                            else if (customImage != null)
                              ListTile(contentPadding: EdgeInsets.zero, leading: const Icon(Icons.image, size: 40), title: Text(customImage!.name), subtitle: const Text('Custom photo'))
                            else if (editing && widget.product?['image_url'] != null)
                              ListTile(contentPadding: EdgeInsets.zero, leading: Image.network(widget.product!['image_url'].toString(), width: 56, height: 56, fit: BoxFit.cover), title: const Text('Current image')),
                            Wrap(spacing: 10, children: [
                              OutlinedButton.icon(onPressed: _chooseLibrary, icon: const Icon(Icons.photo_library_outlined), label: const Text('Image library')),
                              OutlinedButton.icon(onPressed: _chooseCustom, icon: const Icon(Icons.upload_file), label: const Text('Own photo')),
                            ]),
                            const SizedBox(height: 24),
                            FilledButton.icon(
                              onPressed: saving ? null : _save,
                              icon: saving ? const SizedBox.square(dimension: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.send),
                              label: Text(saving ? 'Saving...' : editing ? 'Update & send for review' : 'Add & send for review'),
                            ),
                          ],
                        ),
                      ),
      );
}
