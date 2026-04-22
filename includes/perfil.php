<?php
/**
 * Classe para gerenciar operações de perfil
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

class Perfil {
    private $db;
    private $auth;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->auth = new Auth();
    }
    
    /**
     * Obtém os dados do perfil do cliente
     * 
     * @param int $id_cliente ID do cliente
     * @return array|false Dados do cliente ou false se não encontrado
     */
    public function getPerfilCliente($id_cliente) {
        return $this->db->fetch(
            "SELECT id_cliente, nome, sobrenome, email, telefone, 
                    data_nascimento, cep, estado, cidade, bairro, 
                    endereco, foto_perfil, data_cadastro, ultimo_login 
             FROM clientes 
             WHERE id_cliente = ? AND ativo = 1",
            [$id_cliente]
        );
    }
    
    /**
     * Obtém os dados do perfil da barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @return array|false Dados da barbearia ou false se não encontrada
     */
    public function getPerfilBarbearia($id_barbearia) {
        return $this->db->fetch(
            "SELECT id_barbearia, nome, email, telefone, cnpj, 
                    cep, estado, cidade, bairro, endereco, complemento, 
                    descricao, logo, data_cadastro, ultimo_login, verificado 
             FROM barbearias 
             WHERE id_barbearia = ? AND ativo = 1",
            [$id_barbearia]
        );
    }
    
    /**
     * Obtém as fotos da barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @return array Fotos da barbearia
     */
    public function getFotosBarbearia($id_barbearia) {
        return $this->db->fetchAll(
            "SELECT id_foto, url_foto, descricao, data_upload 
             FROM fotos_barbearia 
             WHERE id_barbearia = ? 
             ORDER BY data_upload DESC",
            [$id_barbearia]
        );
    }
    
    /**
     * Obtém os barbeiros da barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @return array Barbeiros da barbearia
     */
    public function getBarbeirosDaBarbearia($id_barbearia) {
        return $this->db->fetchAll(
            "SELECT id_barbeiro, nome, sobrenome, email, telefone, 
                    foto, anos_experiencia, especialidade, biografia 
             FROM barbeiros 
             WHERE id_barbearia = ? AND ativo = 1 
             ORDER BY nome",
            [$id_barbearia]
        );
    }
    
    /**
     * Atualiza o perfil do cliente
     * 
     * @param int $id_cliente ID do cliente
     * @param array $dados Dados a serem atualizados
     * @return bool Sucesso da operação
     */
    public function atualizarPerfilCliente($id_cliente, $dados) {
        // Verifica se o email já está em uso por outro cliente
        if (isset($dados['email'])) {
            $cliente = $this->db->fetch(
                "SELECT id_cliente FROM clientes WHERE email = ? AND id_cliente != ?",
                [$dados['email'], $id_cliente]
            );
            
            if ($cliente) {
                return false; // Email já está em uso
            }
        }
        
        // Atualiza os dados do cliente
        $result = $this->db->update(
            'clientes',
            $dados,
            'id_cliente = ?',
            [$id_cliente]
        );
        
        // Atualiza os dados na sessão, se o cliente estiver logado
        if ($result && $this->auth->estaLogado() && $this->auth->ehCliente() && $this->auth->getIdUsuarioLogado() == $id_cliente) {
            $cliente = $this->getPerfilCliente($id_cliente);
            $_SESSION['usuario'] = $cliente;
        }
        
        return $result > 0;
    }
    
    /**
     * Atualiza o perfil da barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param array $dados Dados a serem atualizados
     * @return bool Sucesso da operação
     */
    public function atualizarPerfilBarbearia($id_barbearia, $dados) {
        // Verifica se o email já está em uso por outra barbearia
        if (isset($dados['email'])) {
            $barbearia = $this->db->fetch(
                "SELECT id_barbearia FROM barbearias WHERE email = ? AND id_barbearia != ?",
                [$dados['email'], $id_barbearia]
            );
            
            if ($barbearia) {
                return false; // Email já está em uso
            }
        }
        
        // Atualiza os dados da barbearia
        $result = $this->db->update(
            'barbearias',
            $dados,
            'id_barbearia = ?',
            [$id_barbearia]
        );
        
        // Atualiza os dados na sessão, se a barbearia estiver logada
        if ($result && $this->auth->estaLogado() && $this->auth->ehBarbearia() && $this->auth->getIdUsuarioLogado() == $id_barbearia) {
            $barbearia = $this->getPerfilBarbearia($id_barbearia);
            $_SESSION['usuario'] = $barbearia;
        }
        
        return $result > 0;
    }
    
    /**
     * Adiciona uma foto à barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param string $url_foto URL da foto
     * @param string $descricao Descrição da foto
     * @return int|false ID da foto adicionada ou false em caso de erro
     */
    public function adicionarFotoBarbearia($id_barbearia, $url_foto, $descricao = '') {
        return $this->db->insert('fotos_barbearia', [
            'id_barbearia' => $id_barbearia,
            'url_foto' => $url_foto,
            'descricao' => $descricao,
            'data_upload' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Remove uma foto da barbearia
     * 
     * @param int $id_foto ID da foto
     * @param int $id_barbearia ID da barbearia (para verificação)
     * @return bool Sucesso da operação
     */
    public function removerFotoBarbearia($id_foto, $id_barbearia) {
        // Verifica se a foto pertence à barbearia
        $foto = $this->db->fetch(
            "SELECT url_foto FROM fotos_barbearia WHERE id_foto = ? AND id_barbearia = ?",
            [$id_foto, $id_barbearia]
        );
        
        if (!$foto) {
            return false;
        }
        
        // Remove o arquivo físico, se existir
        $caminhoArquivo = $_SERVER['DOCUMENT_ROOT'] . '/' . $foto['url_foto'];
        if (file_exists($caminhoArquivo)) {
            unlink($caminhoArquivo);
        }
        
        // Remove o registro do banco de dados
        return $this->db->delete(
            'fotos_barbearia',
            'id_foto = ? AND id_barbearia = ?',
            [$id_foto, $id_barbearia]
        ) > 0;
    }
    
    /**
     * Adiciona um barbeiro à barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param array $dados Dados do barbeiro
     * @return int|false ID do barbeiro adicionado ou false em caso de erro
     */
    public function adicionarBarbeiro($id_barbearia, $dados) {
        $dados['id_barbearia'] = $id_barbearia;
        return $this->db->insert('barbeiros', $dados);
    }
    
    /**
     * Atualiza os dados de um barbeiro
     * 
     * @param int $id_barbeiro ID do barbeiro
     * @param int $id_barbearia ID da barbearia (para verificação)
     * @param array $dados Dados a serem atualizados
     * @return bool Sucesso da operação
     */
    public function atualizarBarbeiro($id_barbeiro, $id_barbearia, $dados) {
        return $this->db->update(
            'barbeiros',
            $dados,
            'id_barbeiro = ? AND id_barbearia = ?',
            [$id_barbeiro, $id_barbearia]
        ) > 0;
    }
    
    /**
     * Remove um barbeiro
     * 
     * @param int $id_barbeiro ID do barbeiro
     * @param int $id_barbearia ID da barbearia (para verificação)
     * @return bool Sucesso da operação
     */
    public function removerBarbeiro($id_barbeiro, $id_barbearia) {
        return $this->db->update(
            'barbeiros',
            ['ativo' => 0],
            'id_barbeiro = ? AND id_barbearia = ?',
            [$id_barbeiro, $id_barbearia]
        ) > 0;
    }
    
    /**
     * Altera a senha do cliente
     * 
     * @param int $id_cliente ID do cliente
     * @param string $senha_atual Senha atual
     * @param string $nova_senha Nova senha
     * @return bool Sucesso da operação
     */
    public function alterarSenhaCliente($id_cliente, $senha_atual, $nova_senha) {
        // Verifica se a senha atual está correta
        $cliente = $this->db->fetch(
            "SELECT senha FROM clientes WHERE id_cliente = ?",
            [$id_cliente]
        );
        
        if (!$cliente || !password_verify($senha_atual, $cliente['senha'])) {
            return false;
        }
        
        // Atualiza a senha
        return $this->db->update(
            'clientes',
            ['senha' => password_hash($nova_senha, PASSWORD_DEFAULT)],
            'id_cliente = ?',
            [$id_cliente]
        ) > 0;
    }
    
    /**
     * Altera a senha da barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param string $senha_atual Senha atual
     * @param string $nova_senha Nova senha
     * @return bool Sucesso da operação
     */
    public function alterarSenhaBarbearia($id_barbearia, $senha_atual, $nova_senha) {
        // Verifica se a senha atual está correta
        $barbearia = $this->db->fetch(
            "SELECT senha FROM barbearias WHERE id_barbearia = ?",
            [$id_barbearia]
        );
        
        if (!$barbearia || !password_verify($senha_atual, $barbearia['senha'])) {
            return false;
        }
        
        // Atualiza a senha
        return $this->db->update(
            'barbearias',
            ['senha' => password_hash($nova_senha, PASSWORD_DEFAULT)],
            'id_barbearia = ?',
            [$id_barbearia]
        ) > 0;
    }
    
    /**
     * Processa o upload de uma imagem
     * 
     * @param array $arquivo Arquivo enviado ($_FILES['campo'])
     * @param string $diretorio Diretório de destino
     * @param string $prefixo Prefixo para o nome do arquivo
     * @return string|false URL da imagem ou false em caso de erro
     */
    public function uploadImagem($arquivo, $diretorio, $prefixo = '') {
        // Verifica se o arquivo foi enviado corretamente
        if (!isset($arquivo) || $arquivo['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Verifica o tamanho do arquivo
        if ($arquivo['size'] > MAX_FILE_SIZE) {
            return false;
        }
        
        // Obtém a extensão do arquivo
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        
        // Verifica se a extensão é permitida
        if (!in_array($extensao, ALLOWED_EXTENSIONS)) {
            return false;
        }
        
        // Cria o diretório se não existir
        $uploadDir = UPLOAD_DIR . $diretorio;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Gera um nome único para o arquivo
        $nomeArquivo = $prefixo . uniqid() . '.' . $extensao;
        $caminhoArquivo = $uploadDir . $nomeArquivo;
        
        // Move o arquivo para o diretório de uploads
        if (move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
            return UPLOAD_URL . $diretorio . $nomeArquivo;
        }
        
        return false;
    }
}
?>