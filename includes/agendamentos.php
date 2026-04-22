<?php
/**
 * Classe para gerenciar operações de agendamentos
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/horarios.php';

class Agendamentos {
    private $db;
    private $horarios;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->horarios = new Horarios();
    }
    
    /**
     * Obtém os agendamentos de um cliente
     * 
     * @param int $id_cliente ID do cliente
     * @param string $status Status dos agendamentos (opcional)
     * @param string $ordem Ordem dos agendamentos ('futuros', 'passados', 'todos')
     * @return array Agendamentos do cliente
     */
    public function getAgendamentosCliente($id_cliente, $status = null, $ordem = 'futuros') {
        $sql = "SELECT a.*, 
                       b.nome as barbearia_nome, 
                       br.nome as barbeiro_nome, 
                       br.sobrenome as barbeiro_sobrenome,
                       s.nome as servico_nome, 
                       s.preco as servico_preco 
                FROM agendamentos a 
                JOIN barbearias b ON a.id_barbearia = b.id_barbearia 
                JOIN barbeiros br ON a.id_barbeiro = br.id_barbeiro 
                JOIN servicos s ON a.id_servico = s.id_servico 
                WHERE a.id_cliente = ?";
        
        $params = [$id_cliente];
        
        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        if ($ordem === 'futuros') {
            $sql .= " AND (a.data_agendamento > CURDATE() OR (a.data_agendamento = CURDATE() AND a.hora_inicio >= CURTIME()))";
            $sql .= " ORDER BY a.data_agendamento ASC, a.hora_inicio ASC";
        } elseif ($ordem === 'passados') {
            $sql .= " AND (a.data_agendamento < CURDATE() OR (a.data_agendamento = CURDATE() AND a.hora_inicio < CURTIME()))";
            $sql .= " ORDER BY a.data_agendamento DESC, a.hora_inicio DESC";
        } else {
            $sql .= " ORDER BY a.data_agendamento DESC, a.hora_inicio DESC";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtém os agendamentos de uma barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param string $status Status dos agendamentos (opcional)
     * @param string $ordem Ordem dos agendamentos ('futuros', 'passados', 'todos')
     * @param string $data Data específica (formato YYYY-MM-DD, opcional)
     * @return array Agendamentos da barbearia
     */
    public function getAgendamentosBarbearia($id_barbearia, $status = null, $ordem = 'futuros', $data = null) {
        $sql = "SELECT a.*, 
                       c.nome as cliente_nome, 
                       c.sobrenome as cliente_sobrenome,
                       c.telefone as cliente_telefone,
                       br.nome as barbeiro_nome, 
                       br.sobrenome as barbeiro_sobrenome,
                       s.nome as servico_nome, 
                       s.preco as servico_preco 
                FROM agendamentos a 
                JOIN clientes c ON a.id_cliente = c.id_cliente 
                JOIN barbeiros br ON a.id_barbeiro = br.id_barbeiro 
                JOIN servicos s ON a.id_servico = s.id_servico 
                WHERE a.id_barbearia = ?";
        
        $params = [$id_barbearia];
        
        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        if ($data) {
            $sql .= " AND a.data_agendamento = ?";
            $params[] = $data;
        }
        
        if ($ordem === 'futuros') {
            $sql .= " AND (a.data_agendamento > CURDATE() OR (a.data_agendamento = CURDATE() AND a.hora_inicio >= CURTIME()))";
            $sql .= " ORDER BY a.data_agendamento ASC, a.hora_inicio ASC";
        } elseif ($ordem === 'passados') {
            $sql .= " AND (a.data_agendamento < CURDATE() OR (a.data_agendamento = CURDATE() AND a.hora_inicio < CURTIME()))";
            $sql .= " ORDER BY a.data_agendamento DESC, a.hora_inicio DESC";
        } else {
            $sql .= " ORDER BY a.data_agendamento DESC, a.hora_inicio DESC";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtém os agendamentos de um barbeiro
     * 
     * @param int $id_barbeiro ID do barbeiro
     * @param string $status Status dos agendamentos (opcional)
     * @param string $ordem Ordem dos agendamentos ('futuros', 'passados', 'todos')
     * @param string $data Data específica (formato YYYY-MM-DD, opcional)
     * @return array Agendamentos do barbeiro
     */
    public function getAgendamentosBarbeiro($id_barbeiro, $status = null, $ordem = 'futuros', $data = null) {
        $sql = "SELECT a.*, 
                       c.nome as cliente_nome, 
                       c.sobrenome as cliente_sobrenome,
                       c.telefone as cliente_telefone,
                       s.nome as servico_nome, 
                       s.preco as servico_preco 
                FROM agendamentos a 
                JOIN clientes c ON a.id_cliente = c.id_cliente 
                JOIN servicos s ON a.id_servico = s.id_servico 
                WHERE a.id_barbeiro = ?";
        
        $params = [$id_barbeiro];
        
        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        if ($data) {
            $sql .= " AND a.data_agendamento = ?";
            $params[] = $data;
        }
        
        if ($ordem === 'futuros') {
            $sql .= " AND (a.data_agendamento > CURDATE() OR (a.data_agendamento = CURDATE() AND a.hora_inicio >= CURTIME()))";
            $sql .= " ORDER BY a.data_agendamento ASC, a.hora_inicio ASC";
        } elseif ($ordem === 'passados') {
            $sql .= " AND (a.data_agendamento < CURDATE() OR (a.data_agendamento = CURDATE() AND a.hora_inicio < CURTIME()))";
            $sql .= " ORDER BY a.data_agendamento DESC, a.hora_inicio DESC";
        } else {
            $sql .= " ORDER BY a.data_agendamento DESC, a.hora_inicio DESC";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtém um agendamento específico
     * 
     * @param int $id_agendamento ID do agendamento
     * @return array|false Dados do agendamento ou false se não encontrado
     */
    public function getAgendamento($id_agendamento) {
        return $this->db->fetch(
            "SELECT a.*, 
                    c.nome as cliente_nome, 
                    c.sobrenome as cliente_sobrenome,
                    c.telefone as cliente_telefone,
                    b.nome as barbearia_nome,
                    b.telefone as barbearia_telefone,
                    br.nome as barbeiro_nome, 
                    br.sobrenome as barbeiro_sobrenome,
                    s.nome as servico_nome, 
                    s.preco as servico_preco,
                    s.duracao_minutos as servico_duracao
             FROM agendamentos a 
             JOIN clientes c ON a.id_cliente = c.id_cliente 
             JOIN barbearias b ON a.id_barbearia = b.id_barbearia
             JOIN barbeiros br ON a.id_barbeiro = br.id_barbeiro 
             JOIN servicos s ON a.id_servico = s.id_servico 
             WHERE a.id_agendamento = ?",
            [$id_agendamento]
        );
    }
    
    /**
     * Cria um novo agendamento
     * 
     * @param array $dados Dados do agendamento
     * @return int|false ID do agendamento criado ou false em caso de erro
     */
    public function criarAgendamento($dados) {
        // Verifica se o horário está disponível
        if (!$this->verificarDisponibilidade(
            $dados['id_barbearia'],
            $dados['id_barbeiro'],
            $dados['data_agendamento'],
            $dados['hora_inicio'],
            $dados['hora_fim']
        )) {
            return false;
        }
        
        // Insere o agendamento
        return $this->db->insert('agendamentos', $dados);
    }
    
    /**
     * Atualiza o status de um agendamento
     * 
     * @param int $id_agendamento ID do agendamento
     * @param string $status Novo status
     * @param int $id_usuario ID do usuário que está atualizando (cliente ou barbearia)
     * @param string $tipo_usuario Tipo de usuário ('cliente' ou 'barbearia')
     * @return bool Sucesso da operação
     */
    public function atualizarStatus($id_agendamento, $status, $id_usuario, $tipo_usuario) {
        // Obtém o agendamento
        $agendamento = $this->getAgendamento($id_agendamento);
        
        if (!$agendamento) {
            return false;
        }
        
        // Verifica se o usuário tem permissão para atualizar o status
        if ($tipo_usuario === 'cliente' && $agendamento['id_cliente'] != $id_usuario) {
            return false;
        } elseif ($tipo_usuario === 'barbearia' && $agendamento['id_barbearia'] != $id_usuario) {
            return false;
        }
        
        // Verifica se o status é válido
        $status_validos = ['agendado', 'confirmado', 'concluido', 'cancelado_cliente', 'cancelado_barbearia'];
        if (!in_array($status, $status_validos)) {
            return false;
        }
        
        // Verifica se o cliente pode cancelar (apenas agendamentos futuros)
        if ($tipo_usuario === 'cliente' && $status === 'cancelado_cliente') {
            $hoje = date('Y-m-d');
            $agora = date('H:i:s');
            
            if ($agendamento['data_agendamento'] < $hoje || 
                ($agendamento['data_agendamento'] == $hoje && $agendamento['hora_inicio'] <= $agora)) {
                return false;
            }
        }
        
        // Atualiza o status
        return $this->db->update(
            'agendamentos',
            [
                'status' => $status,
                'data_atualizacao' => date('Y-m-d H:i:s')
            ],
            'id_agendamento = ?',
            [$id_agendamento]
        ) > 0;
    }
    
    /**
     * Verifica se um horário está disponível para agendamento
     * 
     * @param int $id_barbearia ID da barbearia
     * @param int $id_barbeiro ID do barbeiro
     * @param string $data Data (formato YYYY-MM-DD)
     * @param string $hora_inicio Hora de início (formato HH:MM)
     * @param string $hora_fim Hora de fim (formato HH:MM)
     * @return bool True se o horário estiver disponível
     */
    public function verificarDisponibilidade($id_barbearia, $id_barbeiro, $data, $hora_inicio, $hora_fim) {
        // Verifica se a data é um dia fechado
        if ($this->horarios->ehDiaFechado($id_barbearia, $data)) {
            return false;
        }
        
        // Obtém o dia da semana (0=Domingo, 1=Segunda, etc.)
        $dia_semana = date('w', strtotime($data));
        
        // Verifica se a barbearia abre neste dia
        $horario_funcionamento = $this->horarios->getHorarioDia($id_barbearia, $dia_semana);
        if (!$horario_funcionamento) {
            return false;
        }
        
        // Verifica se o horário está dentro do horário de funcionamento
        if ($hora_inicio < $horario_funcionamento['hora_abertura'] || $hora_fim > $horario_funcionamento['hora_fechamento']) {
            return false;
        }
        
        // Verifica se o barbeiro já tem agendamentos neste horário
        $agendamentos = $this->db->fetchAll(
            "SELECT * FROM agendamentos 
             WHERE id_barbeiro = ? AND data_agendamento = ? AND status IN ('agendado', 'confirmado') 
             AND ((hora_inicio <= ? AND hora_fim > ?) OR (hora_inicio < ? AND hora_fim >= ?) OR (hora_inicio >= ? AND hora_fim <= ?))",
            [
                $id_barbeiro, 
                $data, 
                $hora_inicio, $hora_inicio, 
                $hora_fim, $hora_fim, 
                $hora_inicio, $hora_fim
            ]
        );
        
        return count($agendamentos) === 0;
    }
    
    /**
     * Obtém os horários disponíveis para agendamento
     * 
     * @param int $id_barbearia ID da barbearia
     * @param int $id_barbeiro ID do barbeiro
     * @param string $data Data (formato YYYY-MM-DD)
     * @param int $duracao_servico Duração do serviço em minutos
     * @return array Horários disponíveis (formato HH:MM)
     */
    public function getHorariosDisponiveis($id_barbearia, $id_barbeiro, $data, $duracao_servico) {
        // Obtém todos os horários possíveis
        $horarios_possiveis = $this->horarios->gerarHorariosDisponiveis($id_barbearia, $data, $duracao_servico);
        
        // Obtém os agendamentos do barbeiro neste dia
        $agendamentos = $this->db->fetchAll(
            "SELECT hora_inicio, hora_fim FROM agendamentos 
             WHERE id_barbeiro = ? AND data_agendamento = ? AND status IN ('agendado', 'confirmado') 
             ORDER BY hora_inicio",
            [$id_barbeiro, $data]
        );
        
        // Filtra os horários disponíveis
        $horarios_disponiveis = [];
        
        foreach ($horarios_possiveis as $horario) {
            $disponivel = true;
            
            foreach ($agendamentos as $agendamento) {
                // Verifica se há sobreposição
                if (($horario['hora_inicio'] >= $agendamento['hora_inicio'] && $horario['hora_inicio'] < $agendamento['hora_fim']) ||
                    ($horario['hora_fim'] > $agendamento['hora_inicio'] && $horario['hora_fim'] <= $agendamento['hora_fim']) ||
                    ($horario['hora_inicio'] <= $agendamento['hora_inicio'] && $horario['hora_fim'] >= $agendamento['hora_fim'])) {
                    $disponivel = false;
                    break;
                }
            }
            
            if ($disponivel) {
                $horarios_disponiveis[] = $horario;
            }
        }
        
        return $horarios_disponiveis;
    }
    
    /**
     * Calcula a hora de fim com base na hora de início e na duração
     * 
     * @param string $hora_inicio Hora de início (formato HH:MM)
     * @param int $duracao_minutos Duração em minutos
     * @return string Hora de fim (formato HH:MM)
     */
    public function calcularHoraFim($hora_inicio, $duracao_minutos) {
        $timestamp = strtotime($hora_inicio) + ($duracao_minutos * 60);
        return date('H:i:s', $timestamp);
    }
}
?>