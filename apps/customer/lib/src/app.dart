import 'package:flutter/material.dart';

import 'core/token_store.dart';
import 'screens/login_screen.dart';
import 'screens/main_shell.dart';
import 'theme/app_theme.dart';

class CNetStoreApp extends StatelessWidget {
  const CNetStoreApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'C-Net Store',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      home: const _AppGate(),
    );
  }
}

class _AppGate extends StatelessWidget {
  const _AppGate();

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<bool>(
      future: const TokenStore().hasToken(),
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Scaffold(body: Center(child: CircularProgressIndicator()));
        }
        return snapshot.data == true ? const MainShell() : const LoginScreen();
      },
    );
  }
}

