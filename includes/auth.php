<?php
/**
 * Funções de autenticação e verificação de sessão
 */

require_once __DIR__ . '/db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Inicia a sessão se ainda não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Autentica um cliente
     * 
     * @param string $email Email do cliente
     * @param string $senha Senha do cliente
     * @return array|false Dados do cliente ou false se a autenticação falhar
     */
    public function loginCliente($email, $senha) {
        $cliente = $this->db->fetch(
            "SELECT * FROM clientes WHERE email = ? AND ativo = 1",
            [$email]
        );
        
        if ($cliente && password_verify($senha, $cliente['senha'])) {
            // Atualiza o último login
            $this->db->update(
                'clientes',
                ['ultimo_login' => date('Y-m-d H:i:s')],
                'id_cliente = ?',
                [$cliente['id_cliente']]
            );
            
            // Remove a senha antes de armazenar na sessão
            unset($cliente['senha']);
            
            // Armazena os dados do cliente na sessão
            $_SESSION['usuario'] = $cliente;
            $_SESSION['tipo_usuario'] = 'cliente';
            $_SESSION['logado'] = true;
            
            return $cliente;
        }
        
        return false;
    }
    
    /**
     * Autentica uma barbearia
     * 
     * @param string $email Email da barbearia
     * @param string $senha Senha da barbearia
     * @return array|false Dados da barbearia ou false se a autenticação falhar
     */
    public function loginBarbearia($email, $senha) {
        $barbearia = $this->db->fetch(
            "SELECT * FROM barbearias WHERE email = ? AND ativo = 1",
            [$email]
        );
        
        if ($barbearia && password_verify($senha, $barbearia['senha'])) {
            // Atualiza o último login
            $this->db->update(
                'barbearias',
                ['ultimo_login' => date('Y-m-d H:i:s')],
                'id_barbearia = ?',
                [$barbearia['id_barbearia']]
            );
            
            // Remove a senha antes de armazenar na sessão
            unset($barbearia['senha']);
            
            // Armazena os dados da barbearia na sessão
            $_SESSION['usuario'] = $barbearia;
            $_SESSION['tipo_usuario'] = 'barbearia';
            $_SESSION['logado'] = true;
            
            return $barbearia;
        }
        
        return false;
    }
    
    /**
     * Registra um novo cliente
     * 
     * @param array $dados Dados do cliente
     * @return int|false ID do cliente registrado ou false em caso de erro
     */
    public function registrarCliente($dados) {
        // Verifica se o email já está em uso
        $existente = $this->db->fetch(
            "SELECT id_cliente FROM clientes WHERE email = ?",
            [$dados['email']]
        );
        
        if ($existente) {
            return false; // Email já está em uso
        }
        
        // Hash da senha
        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        // Insere o cliente no banco de dados
        return $this->db->insert('clientes', $dados);
    }
    
    /**
     * Registra uma nova barbearia
     * 
     * @param array $dados Dados da barbearia
     * @return int|false ID da barbearia registrada ou false em caso de erro
     */
    public function registrarBarbearia($dados) {
        // Verifica se o email já está em uso
        $existente = $this->db->fetch(
            "SELECT id_barbearia FROM barbearias WHERE email = ?",
            [$dados['email']]
        );
        
        if ($existente) {
            return false; // Email já está em uso
        }
        
        // Hash da senha
        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        // Insere a barbearia no banco de dados
        return $this->db->insert('barbearias', $dados);
    }
    
    /**
     * Verifica se o usuário está logado
     * 
     * @return bool
     */
    public function estaLogado() {
        return isset($_SESSION['logado']) && $_SESSION['logado'] === true;
    }
    
    /**
     * Verifica se o usuário logado é um cliente
     * 
     * @return bool
     */
    public function ehCliente() {
        return $this->estaLogado() && $_SESSION['tipo_usuario'] === 'cliente';
    }
    
    /**
     * Verifica se o usuário logado é uma barbearia
     * 
     * @return bool
     */
    public function ehBarbearia() {
        return $this->estaLogado() && $_SESSION['tipo_usuario'] === 'barbearia';
    }
    
    /**
     * Obtém os dados do usuário logado
     * 
     * @return array|null
     */
    public function getUsuarioLogado() {
        return $this->estaLogado() ? $_SESSION['usuario'] : null;
    }
    
    /**
     * Obtém o ID do usuário logado
     * 
     * @return int|null
     */
    public function getIdUsuarioLogado() {
        if (!$this->estaLogado()) {
            return null;
        }
        
        if ($this->ehCliente()) {
            return $_SESSION['usuario']['id_cliente'];
        } else {
            return $_SESSION['usuario']['id_barbearia'];
        }
    }
    
    /**
     * Encerra a sessão do usuário
     */
    public function logout() {
        // Limpa todas as variáveis de sessão
        $_SESSION = [];
        
        // Destrói o cookie da sessão
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Destrói a sessão
        session_destroy();
    }
    
    /**
     * Redireciona o usuário se não estiver logado
     * 
     * @param string $url URL para redirecionamento
     */
    public function verificarLogin($url = 'login.html') {
        if (!$this->estaLogado()) {
            header("Location: $url");
            exit;
        }
    }
    
    /**
     * Redireciona o usuário se não for um cliente
     * 
     * @param string $url URL para redirecionamento
     */
    public function verificarCliente($url = 'login.html') {
        if (!$this->ehCliente()) {
            header("Location: $url");
            exit;
        }
    }
    
    /**
     * Redireciona o usuário se não for uma barbearia
     * 
     * @param string $url URL para redirecionamento
     */
    public function verificarBarbearia($url = 'login.html') {
        if (!$this->ehBarbearia()) {
            header("Location: $url");
            exit;
        }
    }
}
?>