import 'package:flutter/material.dart';

import '../core/api_client.dart';

class ImageLibraryScreen extends StatefulWidget {
  const ImageLibraryScreen({super.key, this.categoryId});

  final int? categoryId;

  @override
  State<ImageLibraryScreen> createState() => _ImageLibraryScreenState();
}

class _ImageLibraryScreenState extends State<ImageLibraryScreen> {
  final search = TextEditingController();
  String? group;
  late Future<List<dynamic>> groups;
  late Future<Map<String, dynamic>> assets;

  @override
  void initState() {
    super.initState();
    groups = ApiClient().productImageGroups();
    assets = _fetch();
  }

  Future<Map<String, dynamic>> _fetch() => ApiClient().productImageLibrary(
        search: search.text.trim(),
        group: group,
        categoryId: widget.categoryId,
      );

  void reload() => setState(() => assets = _fetch());

  @override
  void dispose() {
    search.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('Smart Image Library')),
        body: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                children: [
                  TextField(
                    controller: search,
                    textInputAction: TextInputAction.search,
                    onSubmitted: (_) => reload(),
                    decoration: InputDecoration(
                      hintText: 'Search product images',
                      prefixIcon: const Icon(Icons.search),
                      suffixIcon: IconButton(
                        onPressed: reload,
                        icon: const Icon(Icons.arrow_forward),
                      ),
                    ),
                  ),
                  FutureBuilder<List<dynamic>>(
                    future: groups,
                    builder: (_, snapshot) => SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          ChoiceChip(
                            label: const Text('All'),
                            selected: group == null,
                            onSelected: (_) { group = null; reload(); },
                          ),
                          ...?snapshot.data?.map(
                            (item) => Padding(
                              padding: const EdgeInsets.only(left: 7),
                              child: ChoiceChip(
                                label: Text(item.toString()),
                                selected: group == item.toString(),
                                onSelected: (_) { group = item.toString(); reload(); },
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: FutureBuilder<Map<String, dynamic>>(
                future: assets,
                builder: (_, snapshot) {
                  if (snapshot.connectionState != ConnectionState.done) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  if (snapshot.hasError) {
                    return Center(child: Text('Unable to load images: ${snapshot.error}'));
                  }
                  final page = snapshot.data?['data'] as Map<String, dynamic>?;
                  final rows = page?['data'] as List<dynamic>? ?? <dynamic>[];
                  if (rows.isEmpty) {
                    return const Center(child: Text('No approved images match this category.'));
                  }
                  return GridView.builder(
                    padding: const EdgeInsets.all(12),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 10,
                      mainAxisSpacing: 10,
                      childAspectRatio: .82,
                    ),
                    itemCount: rows.length,
                    itemBuilder: (_, index) {
                      final asset = rows[index] as Map<String, dynamic>;
                      return InkWell(
                        onTap: () => Navigator.pop(context, asset),
                        child: Card(
                          clipBehavior: Clip.antiAlias,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Expanded(child: Image.network(asset['image_url'].toString(), width: double.infinity, fit: BoxFit.cover)),
                              Padding(padding: const EdgeInsets.all(9), child: Text(asset['name']?.toString() ?? 'Product image', maxLines: 2, overflow: TextOverflow.ellipsis)),
                            ],
                          ),
                        ),
                      );
                    },
                  );
                },
              ),
            ),
          ],
        ),
      );
}
